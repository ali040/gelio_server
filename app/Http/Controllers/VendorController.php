<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Zone;
use App\Models\Store;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Validator;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\BusinessSetting;

class VendorController extends Controller
{
    public function create()
    {
        $status = BusinessSetting::where('key', 'toggle_store_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            Toastr::error(trans('messages.not_found'));
            return back();
        }
        return view('vendor-views.auth.register');
    }

    public function store(Request $request)
    {
        $status = BusinessSetting::where('key', 'toggle_store_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            Toastr::error(trans('messages.not_found'));
            return back();
        }
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'email' => 'required|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|unique:vendors',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'password' => 'required|min:6',
            'zone_id' => 'required',
            'module_id' => 'required',
            'logo' => 'required',
            'tax' => 'required',
        ]);

        if($request->zone_id)
        {
            $point = new Point($request->latitude, $request->longitude);
            $zone = Zone::contains('coordinates', $point)->where('id', $request->zone_id)->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', trans('messages.coordinates_out_of_zone'));
                return back()->withErrors($validator)
                        ->withInput();
            }
        }
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        $vendor = new Vendor();
        $vendor->f_name = $request->f_name;
        $vendor->l_name = $request->l_name;
        $vendor->email = $request->email;
        $vendor->phone = $request->phone;
        $vendor->password = bcrypt($request->password);
        $vendor->status = null;
        $vendor->save();

        $store = new Store;
        $store->name = $request->name;
        $store->phone = $request->phone;
        $store->email = $request->email;
        $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        $store->address = $request->address;
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->vendor_id = $vendor->id;
        $store->zone_id = $request->zone_id;
        $store->module_id = $request->module_id;
        $store->tax = $request->tax;
        $store->delivery_time = $request->minimum_delivery_time .'-'. $request->maximum_delivery_time.' '.$request->delivery_time_type;
        $store->status = 0;
        $store->save();
        Toastr::success(trans('messages.application_placed_successfully'));
        return back();
    }
}
