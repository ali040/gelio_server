<?php

namespace App\CentralLogics;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\AdminWallet;
use App\Models\StoreWallet;
use App\Models\DeliveryManWallet;
use Illuminate\Support\Facades\DB;

class OrderLogic
{
    public static function gen_unique_id()
    {
        return rand(1000, 9999) . '-' . Str::random(5) . '-' . time();
    }
    
    public static function track_order($order_id)
    {
        return Helpers::order_data_formatting(Order::with(['details', 'delivery_man.rating'])->where(['id' => $order_id])->first(), false);
    }

    // public static function place_order($customer_id, $email, $customer_info, $cart, $payment_method, $discount, $coupon_code = null)
    // {

    // }

    public static function updated_order_calculation($order)
    {
        return true;
    }
    public static function create_transaction($order, $received_by=false, $status = null)
    {
        $type = $order->order_type;
        if($type=='parcel')
        {
            $comission = \App\Models\BusinessSetting::where('key','parcel_commission_dm')->first()->value;
            $order_amount = $order->order_amount;
            $dm_commission = $comission?($order_amount/ 100) * $comission:0;            
            $comission_amount = $order_amount - $dm_commission;            
        }
        else
        {
            $comission = $order->store->comission==null?\App\Models\BusinessSetting::where('key','admin_commission')->first()->value:$order->store->comission;
            $order_amount = $order->order_amount - $order->delivery_charge - $order->total_tax_amount;
            $comission_amount = $comission?($order_amount/ 100) * $comission:0;
            $dm_commission = $order->original_delivery_charge;
        }

        try{
            OrderTransaction::insert([
                'vendor_id' =>$type=='parcel'?null:$order->store->vendor->id,
                'delivery_man_id'=>$order->delivery_man_id,
                'order_id' =>$order->id,
                'order_amount'=>$order->order_amount,
                'store_amount'=>$order_amount + $order->total_tax_amount - $comission_amount,
                'admin_commission'=>$comission_amount,
                'delivery_charge'=>$order->delivery_charge,
                'original_delivery_charge'=>$dm_commission,
                'tax'=>$order->total_tax_amount,
                'received_by'=> $received_by?$received_by:'admin',
                'zone_id'=>$order->zone_id,
                'module_id'=>$order->module_id,
                'status'=> $status,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => Admin::where('role_id', 1)->first()->id]
            );



            $adminWallet->total_commission_earning = $adminWallet->total_commission_earning+$comission_amount;

            if($type != 'parcel')
            {
                $vendorWallet = StoreWallet::firstOrNew(
                    ['vendor_id' => $order->store->vendor->id]
                );
                if($order->store->self_delivery_system)
                {
                    $vendorWallet->total_earning = $vendorWallet->total_earning + $order->delivery_charge;
                }
                else{
                    $adminWallet->delivery_charge = $adminWallet->delivery_charge+$order->delivery_charge;
                }
                $vendorWallet->total_earning = $vendorWallet->total_earning+($order_amount + $order->total_tax_amount - $comission_amount);                
            }


            try
            {
                DB::beginTransaction();
                if($received_by=='admin')
                {
                    $adminWallet->digital_received = $adminWallet->digital_received+$order->order_amount;
                }
                else if($received_by=='store' && $type != 'parcel')
                {
                    $vendorWallet->collected_cash = $vendorWallet->collected_cash+$order->order_amount;
                }
                else if($received_by==false)
                {
                    $adminWallet->manual_received = $adminWallet->manual_received+$order->order_amount;
                }
                else if($received_by=='deliveryman' && $order->delivery_man->type == 'zone_wise')
                {
                    $dmWallet = DeliveryManWallet::firstOrNew(
                        ['delivery_man_id' => $order->delivery_man_id]
                    );
                    $dmWallet->collected_cash=$dmWallet->collected_cash+$order->order_amount;
                    $dmWallet->save();
                }
                // else if($order->store->self_delivery_system)
                // {
                //     $vendorWallet->collected_cash = $vendorWallet->collected_cash+$order->order_amount - $order->delivery_charge;
                // }
                $adminWallet->save();
                if($type != 'parcel')
                {
                    $vendorWallet->save();
                }
                
                DB::commit();
            }
            catch(\Exception $e)
            {
                DB::rollBack();
                info($e);
                return false;
            }
        }
        catch(\Exception $e){
            info($e);
            return false;
        }

        return true;
    }

    public static function refund_order($order)
    {
        $order_transaction = $order->transaction;
        if($order_transaction == null || $order->store == null)
        {
            return false;
        }
        $received_by = $order_transaction->received_by;

        $adminWallet = AdminWallet::firstOrNew(
            ['admin_id' => Admin::where('role_id', 1)->first()->id]
        );

        $vendorWallet = StoreWallet::firstOrNew(
            ['vendor_id' => $order->store->vendor->id]
        );

        
        $adminWallet->total_commission_earning = $adminWallet->total_commission_earning - $order_transaction->admin_commission;

        $vendorWallet->total_earning = $vendorWallet->total_earning - $order_transaction->restaurant_amount;

        $refund_amount = $order->order_amount;

        $status = 'refunded_with_delivery_charge';
        if($order->order_status == 'delivered')
        {
            $refund_amount = $order->order_amount - $order->delivery_charge;
            $status = 'refunded_without_delivery_charge';
        }
        else
        {
            $adminWallet->delivery_charge = $adminWallet->delivery_charge - $order_transaction->delivery_charge;
        }
        try
        {
            DB::beginTransaction();
            if($received_by=='admin')
            {
                if($order->delivery_man_id && $order->payment_method != "cash_on_delivery")
                {
                    $adminWallet->digital_received = $adminWallet->digital_received - $refund_amount;
                }
                else
                {
                    $adminWallet->manual_received = $adminWallet->manual_received - $refund_amount;
                }
                
            }
            else if($received_by=='store')
            {
                $vendorWallet->collected_cash = $vendorWallet->collected_cash - $refund_amount;
            }

                // DB::table('account_transactions')->insert([
                //     'from_type'=>'customer',
                //     'from_id'=>$order->user_id,
                //     'current_balance'=> 0,
                //     'amount'=> $refund_amount,
                //     'method'=>'CASH',
                //     'created_at' => now(),
                //     'updated_at' => now()
                // ]);
 
            else if($received_by=='deliveryman')
            {
                $dmWallet = DeliveryManWallet::firstOrNew(
                    ['delivery_man_id' => $order->delivery_man_id]
                );
                $dmWallet->collected_cash=$dmWallet->collected_cash - $refund_amount;
                $dmWallet->save();
            }
            $order_transaction->status = $status;
            $order_transaction->save();
            $adminWallet->save();
            $vendorWallet->save();
            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            info($e);
            return false;
        }
        return true;

    }

    public static function format_export_data($orders, $type='order')
    {
        $data = [];
        foreach($orders as $key=>$order)
        {

            $data[]=[
                '#'=>$key+1,
                trans('messages.order')=>$order['id'],
                trans('messages.date')=>date('d M Y',strtotime($order['created_at'])),
                trans('messages.customer')=>$order->customer?$order->customer['f_name'].' '.$order->customer['l_name']:__('messages.invalid').' '.__('messages.customer').' '.__('messages.data'),
                trans($type=='order'?'messages.store':'messages.parcel_category')=>\Str::limit($type=='order'?($order->store?$order->store->name:__('messages.store deleted!')):($order->parcel_category?$order->parcel_category->name:__('messages.not_found')),20,'...'),
                trans('messages.payment').' '.trans('messages.status')=>$order->payment_status=='paid'?__('messages.paid'):__('messages.unpaid'),
                trans('messages.total')=>\App\CentralLogics\Helpers::format_currency($order['order_amount']),
                trans('messages.order').' '.trans('messages.status')=>trans('messages.'. $order['order_status']),
                trans('messages.order').' '.trans('messages.type')=>trans('messages.'.$order['order_type'])
            ];
        }
        return $data;
    }
}
