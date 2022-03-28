@extends('layouts.admin.app')

@section('title',__('messages.modules'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{__('messages.module')}} {{__('messages.type')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header"><h5>{{__('messages.add').' '.__('messages.new')}} {{__('messages.module')}}</h5></div>
            <div class="card-body">
                <form action="{{route('admin.module.create')}}" method="get" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{__('messages.type')}}</label>
                        <input type="text" name="module_type" class="form-control" placeholder="{{__('messages.new_category')}}" value="{{old('name')}}" required maxlength="191">
                    </div>

                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{__('messages.description')}}</label>
                        <textarea class="ckeditor form-control" name="module_description"></textarea>
                    </div>

                    <div class="form-group pt-2">
                        <button type="submit" class="btn btn-primary">{{__('messages.add')}}</button>
                    </div>
                    
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header pb-0">
                <h5>{{__('messages.module_type')}} {{__('messages.list')}}</h5>
                {{--<form id="dataSearch">
                    @csrf
                    <!-- Search -->
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="tio-search"></i>
                            </div>
                        </div>
                        <input type="search" name="search" class="form-control" placeholder="{{__('messages.search_categories')}}" aria-label="{{__('messages.search_categories')}}">
                        <button type="submit" class="btn btn-light">{{__('messages.search')}}</button>
                    </div>
                    <!-- End Search -->
                </form>--}}
            </div>
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-align-middle" style="width:100%;"
                        data-hs-datatables-options='{
                            "isResponsive": false,
                            "isShowPaging": false,
                            "paging":false,
                        }'>
                        <thead class="thead-light">
                            <tr>
                                <th>{{__('messages.module_type')}}</th>
                                <th>{{__('messages.description')}}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                        @foreach($module_type as $key=>$module)
                            <tr>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($module['module_type'], 20,'...')}}
                                    </span>
                                </td>
                                <td>
                                    {!! $module->description !!}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('script_2')
    <script src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.ckeditor').ckeditor();
        });
    </script>
@endpush
