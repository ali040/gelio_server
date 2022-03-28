<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleType;
use App\Models\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modules = Module::latest()->paginate(config('default_pagination'));
        
        return view('admin-views.module.index',compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('admin-views.module.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_name' => 'required|unique:modules|max:100',
        ], [
            'module_name.required' => trans('messages.Name is required!'),
        ]);

        $module = new Module();
        $module->module_name = $request->module_name[array_search('en', $request->lang)];
        $module->thumbnail = Helpers::upload('module/', 'png', $request->file('image'));
        $module->module_type= $request->module_type;
        $module->save();

        $data = [];
        foreach($request->lang as $index=>$key)
        {
            if($request->module_name[$index] && $key != 'en')
            {
                array_push($data, Array(
                    'translationable_type'  => 'App\Models\Module',
                    'translationable_id'    => $module->id,
                    'locale'                => $key,
                    'key'                   => 'module_name',
                    'value'                 => $request->module_name[$index],
                ));
            }
        }
        if(count($data))
        {
            Translation::insert($data);
        }

        Toastr::success(trans('messages.module_updated_successfully'));
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $module = Module::findOrFail($id);
        return response()->json(['data'=>config('module.'.$module->module_type)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $module = Module::withoutGlobalScope('translate')->findOrFail($id);
        return view('admin-views.module.edit', compact('module'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'module_name' => 'required|max:100|unique:modules,module_name,'.$id,
        ], [
            'module_name.required' => trans('messages.Name is required!'),
        ]);
        $module = Module::withoutGlobalScope('translate')->findOrFail($id);

        $module->module_name = $request->module_name[array_search('en', $request->lang)];
        $module->thumbnail = $request->has('image') ? Helpers::update('module/', $module->thumbnail, 'png', $request->file('image')) : $module->thumbnail;
        // $module->module_type = $request->module_type;
        $module->save();
        foreach($request->lang as $index=>$key)
        {
            if($request->module_name[$index] && $key != 'en')
            {
                Translation::updateOrInsert(
                    ['translationable_type'  => 'App\Models\Module',
                        'translationable_id'    => $module->id,
                        'locale'                => $key,
                        'key'                   => 'module_name'],
                    ['value'                 => $request->module_name[$index]]
                );
            }
        }
        Toastr::success(trans('messages.category_updated_successfully'));
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $module = Module::withoutGlobalScope('translate')->findOrFail($id);
        if($module->thumbnail)
        {
            if (Storage::disk('public')->exists('module/' . $module['thumbnail'])) {
                Storage::disk('public')->delete('module/' . $module['thumbnail']);
            }
        }
        $module->translations()->delete();
        $module->delete();
        Toastr::success(trans('messages.module_deleted_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $module = Module::find($request->id);
        $module->status = $request->status;
        $module->save();
        Toastr::success(trans('messages.module_status_updated'));
        return back();
    }

    public function type(Request $request)
    {
        return response()->json(['data'=>config('module.'.$request->module_type)]);
    }
}
