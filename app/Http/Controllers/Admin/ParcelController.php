<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\Order;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class ParcelController extends Controller
{
    public function orders(Request $request, $status= 'all')
    {
        if (session()->has('zone_filter') == false) {
            session()->put('zone_filter', 0);
        }

        if(session()->has('order_filter'))
        {
            $request = json_decode(session('order_filter'));
        }

        $key = $request['search']?explode(' ', $request['search']):null;
        
        Order::where(['checked' => 0,'order_type'=>'parcel'])->update(['checked' => 1]);

        $orders = Order::with(['customer', 'store'])
        ->when(isset($key),function($query)use($key){
            return $query->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('order_status', 'like', "%{$value}%")
                    ->orWhere('transaction_reference', 'like', "%{$value}%");
                }
            });
        })
        ->when($request->query('module_id', null), function($query)use($request){
            return $query->module($request->query('module_id'));
        })
        ->when(isset($request->zone), function($query)use($request){
            return $query->where('zone_id',$request->zone);
        })
        ->when($status == 'scheduled', function($query){
            return $query->whereRaw('created_at <> schedule_at');
        })
        ->when($status == 'searching_for_deliverymen', function($query){
            return $query->SearchingForDeliveryman();
        })
        ->when($status == 'pending', function($query){
            return $query->Pending();
        })
        ->when($status == 'accepted', function($query){
            return $query->AccepteByDeliveryman();
        })
        ->when($status == 'processing', function($query){
            return $query->Preparing();
        })
        ->when($status == 'food_on_the_way', function($query){
            return $query->ItemOnTheWay();
        })
        ->when($status == 'delivered', function($query){
            return $query->Delivered();
        })
        ->when($status == 'canceled', function($query){
            return $query->Canceled();
        })
        ->when($status == 'failed', function($query){
            return $query->failed();
        })
        ->when($status == 'refunded', function($query){
            return $query->Refunded();
        })
        ->when($status == 'scheduled', function($query){
            return $query->Scheduled();
        })
        ->when($status == 'on_going', function($query){
            return $query->Ongoing();
        })
        ->when(($status != 'all' && $status != 'scheduled' && $status != 'canceled' && $status != 'refund_requested' && $status != 'refunded' && $status != 'delivered' && $status != 'failed'), function($query){
            return $query->OrderScheduledIn(30);
        })
        ->when(isset($request->vendor), function($query)use($request){
            return $query->whereHas('store', function($query)use($request){
                return $query->whereIn('id',$request->vendor);
            });
        })
        ->when(isset($request->orderStatus) && $status == 'all', function($query)use($request){
            return $query->whereIn('order_status',$request->orderStatus);
        })
        ->when(isset($request->scheduled) && $status == 'all', function($query){
            return $query->scheduled();
        })
        ->when(isset($request->order_type), function($query)use($request){
            return $query->where('order_type', $request->order_type);
        })
        ->when(isset($request->from_date)&&isset($request->to_date)&&$request->from_date!=null&&$request->to_date!=null, function($query)use($request){
            return $query->whereBetween('created_at', [$request->from_date." 00:00:00",$request->to_date." 23:59:59"]);
        })
        ->ParcelOrder()
        ->orderBy('schedule_at', 'desc')
        ->paginate(config('default_pagination'));
        $orderstatus = isset($request->orderStatus)?$request->orderStatus:[];
        $scheduled =isset($request->scheduled)?$request->scheduled:0;
        $vendor_ids =isset($request->vendor)?$request->vendor:[];
        $zone_ids =isset($request->zone)?$request->zone:[];
        $from_date =isset($request->from_date)?$request->from_date:null;
        $to_date =isset($request->to_date)?$request->to_date:null;
        $order_type =isset($request->order_type)?$request->order_type:null;
        $total = $orders->total();

        return view('admin-views.order.list', compact('orders', 'status', 'orderstatus', 'scheduled', 'vendor_ids', 'zone_ids', 'from_date', 'to_date', 'total', 'order_type'));
    }

    public function order_details(Request $request, $id)
    {
        $order = Order::with(['customer'=>function($query){
            return $query->withCount('orders');
        },'delivery_man'=>function($query){
            return $query->withCount('orders');
        }])->where(['id' => $id])->ParcelOrder()->first();
        if (isset($order)) {
            $deliveryMen = DeliveryMan::where('zone_id',$order->zone_id)->available()->active()->get();
            $category = $request->query('category_id', 0);
            $categories = [];
            $products = [];
            $editing=false;
            $deliveryMen=Helpers::deliverymen_list_formatting($deliveryMen);
            $keyword = null;
            return view('admin-views.order.order-view', compact('order', 'deliveryMen','categories', 'products','category', 'keyword', 'editing'));
        } else {
            Toastr::info(trans('messages.no_more_orders'));
            return back();
        }
    }

    public function settings()
    {
        return view('admin-views.parcel.settings');
    }

    public function update_settings(Request $request)
    {
        $request->validate([
            'parcel_per_km_shipping_charge'=>'required|numeric|min:0',
            'parcel_minimum_shipping_charge'=>'required|numeric|min:0',
            'parcel_commission_dm'=>'required|numeric|min:0',
        ],[
            'parcel_commission_dm.required'=>trans('validation.required',['attribute'=>trans('messages.deliveryman_commission')]),
            'parcel_commission_dm.numeric'=>trans('validation.numeric',['attribute'=>trans('messages.deliveryman_commission')]),
            'parcel_commission_dm.min'=>trans('validation.min',['attribute'=>trans('messages.deliveryman_commission')]),

            'parcel_per_km_shipping_charge.required'=>trans('validation.required',['attribute'=>trans('messages.per_km_shipping_charge')]),
            'parcel_per_km_shipping_charge.numeric'=>trans('validation.numeric',['attribute'=>trans('messages.per_km_shipping_charge')]),
            'parcel_per_km_shipping_charge.min'=>trans('validation.min',['attribute'=>trans('messages.per_km_shipping_charge')]),

            'parcel_minimum_shipping_charge.required'=>trans('validation.required',['attribute'=>trans('messages.minimum_shipping_charge')]),
            'parcel_minimum_shipping_charge.numeric'=>trans('validation.numeric',['attribute'=>trans('messages.minimum_shipping_charge')]),
            'parcel_minimum_shipping_charge.min'=>trans('validation.min',['attribute'=>trans('messages.minimum_shipping_charge')]),
        ]);
        BusinessSetting::updateOrinsert(['key'=>'parcel_per_km_shipping_charge'],['value'=>$request->parcel_per_km_shipping_charge]);
        BusinessSetting::updateOrinsert(['key'=>'parcel_minimum_shipping_charge'],['value'=>$request->parcel_minimum_shipping_charge]);
        BusinessSetting::updateOrinsert(['key'=>'parcel_commission_dm'],['value'=>$request->parcel_commission_dm]);

        Toastr::success(trans('messages.parcel_settings_updated'));
        return back();
    }
}
