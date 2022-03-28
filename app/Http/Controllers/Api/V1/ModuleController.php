<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;


class ModuleController extends Controller
{

    public function index(Request $request)
    {
        $modules = Module::active()->get();
        $modules = array_map(function($item){
            if(count($item['translations'])>0)
            {
                $item['module_name'] = $item['translations'][0]['value'];
            }
            return $item;
        },$modules->toArray());
        return response()->json($modules);
    }

}
