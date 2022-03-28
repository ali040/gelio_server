@extends('layouts.admin.app')

@section('title',$store->name."'s Orders")

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
<div class="content container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{__('messages.dashboard')}}</a></li>
            <li class="breadcrumb-item" aria-current="page">{{__('messages.vendor_view')}}</li>
        </ol>
    </nav>

    @include('admin-views.vendor.view.partials._header',['store'=>$store])
    <!-- Page Heading -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="order">
            <div class="row pt-2">
                <div class="col-md-12">
                    <div class="card w-100">
                        <div class="card-header">
                            {{__('messages.order')}} {{__('messages.info')}}
                        </div>
                        <!-- Card -->
                        <div class="card-body mb-3 mb-lg-5">
                            <div class="row gx-lg-4">
                                <div class="col-sm-6 col-lg-3">
                                    <div class="media" style="cursor: pointer" onclick="location.href='{{route('admin.order.list',['pending'])}}?vendor[]={{$store->id}}'">
                                        <div class="media-body">
                                            <h6 class="card-subtitle">{{__('messages.pending')}}</h6>
                                            <span class="card-title h3">
                                            {{\App\Models\Order::where(['order_status'=>'pending','store_id'=>$store->id])->count()}}</span>
                                        </div>
                                        <span class="icon icon-sm icon-soft-secondary icon-circle ml-3">
                                        <i class="tio-airdrop"></i>
                                        </span>
                                    </div>
                                    <div class="d-lg-none">
                                        <hr>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 column-divider-sm">
                                    <div class="media" style="cursor: pointer" onclick="location.href='{{route('admin.order.list',['delivered'])}}?vendor[]={{$store->id}}'">
                                        <div class="media-body">
                                            <h6 class="card-subtitle">{{__('messages.delivered')}}</h6>
                                            <span class="card-title h3">{{\App\Models\Order::where(['order_status'=>'delivered', 'store_id'=>$store->id])->count()}}</span>
                                        </div>
                                        <span class="icon icon-sm icon-soft-secondary icon-circle ml-3">
                                        <i class="tio-checkmark-circle"></i>
                                        </span>
                                    </div>
                                    <div class="d-lg-none">
                                        <hr>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 column-divider-lg">
                                    <div class="media" style="cursor: pointer" onclick="location.href='{{route('admin.order.list',['scheduled'])}}?vendor[]={{$store->id}}'">
                                        <div class="media-body">
                                            <h6 class="card-subtitle">{{__('messages.scheduled')}}</h6>
                                            <span class="card-title h3">{{\App\Models\Order::Scheduled()->where('store_id', $store->id)->count()}}</span>
                                        </div>
                                        <span class="icon icon-sm icon-soft-secondary icon-circle ml-3">
                                        <i class="tio-clock"></i>
                                        </span>
                                    </div>
                                    <div class="d-lg-none">
                                        <hr>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-3 column-divider-sm">
                                    <div class="media" style="cursor: pointer" onclick="location.href='{{route('admin.order.list',['all'])}}?vendor[]={{$store->id}}'">
                                        <div class="media-body">
                                            <h6 class="card-subtitle">{{__('messages.all')}}</h6>
                                            <span class="card-title h3">{{\App\Models\Order::where('store_id', $store->id)->count()}}</span>
                                        </div>
                                        <span class="icon icon-sm icon-soft-secondary icon-circle ml-3">
                                        <i class="tio-table"></i>
                                        </span>
                                    </div>
                                    <div class="d-lg-none">
                                        <hr>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table id="datatable"
                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                style="width: 100%"
                                data-hs-datatables-options='{
                                "columnDefs": [{
                                    "targets": [0],
                                    "orderable": false
                                }],
                                "order": [],
                                "info": {
                                "totalQty": "#datatableWithPaginationInfoTotalQty"
                                },
                                "search": "#datatableSearch",
                                "entries": "#datatableEntries",
                                "pageLength": 25,
                                "isResponsive": false,
                                "isShowPaging": false,
                                "pagination": "datatablePagination"
                            }'>
                                <thead class="thead-light">
                                <tr>
                                    <th class="">
                                        {{__('messages.#')}}
                                    </th>
                                    <th class="table-column-pl-0">{{__('messages.order')}}</th>
                                    <th>{{__('messages.date')}}</th>
                                    <th>{{__('messages.customer')}}</th>
                                    <th>{{__('messages.payment')}} {{__('messages.status')}}</th>
                                    <th>{{__('messages.total')}}</th>
                                    <th>{{__('messages.order')}} {{__('messages.status')}}</th>
                                    <th>{{__('messages.actions')}}</th>
                                </tr>
                                </thead>

                                <tbody id="set-rows">
                                @php($orders=\App\Models\Order::where('store_id', $store->id)->latest()->Notpos()->paginate(10))
                                @foreach($orders as $key=>$order)

                                    <tr class="status-{{$order['order_status']}} class-all">
                                        <td class="">
                                            {{$key+ $orders->firstItem()}}
                                        </td>
                                        <td class="table-column-pl-0">
                                            <a href="{{route('admin.order.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                        </td>
                                        <td>{{date('d M Y',strtotime($order['created_at']))}}</td>
                                        <td>
                                            @if($order->customer)
                                                <a class="text-body text-capitalize"
                                                href="{{route('admin.customer.view',[$order['user_id']])}}">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</a>
                                            @else
                                                <label class="badge badge-danger">{{__('messages.invalid')}} {{__('messages.customer')}} {{__('messages.data')}}</label>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->payment_status=='paid')
                                                <span class="badge badge-soft-success">
                                                <span class="legend-indicator bg-success"></span>{{__('messages.paid')}}
                                                </span>
                                            @else
                                                <span class="badge badge-soft-danger">
                                                <span class="legend-indicator bg-danger"></span>{{__('messages.unpaid')}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{\App\CentralLogics\Helpers::format_currency($order['order_amount'])}}</td>
                                        <td class="text-capitalize">
                                            @if($order['order_status']=='pending')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                                <span class="legend-indicator bg-info"></span>{{__('messages.pending')}}
                                                </span>
                                            @elseif($order['order_status']=='confirmed')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                                <span class="legend-indicator bg-info"></span>{{__('messages.confirmed')}}
                                                </span>
                                            @elseif($order['order_status']=='processing')
                                                <span class="badge badge-soft-warning ml-2 ml-sm-3">
                                                <span class="legend-indicator bg-warning"></span>{{__('messages.processing')}}
                                                </span>
                                            @elseif($order['order_status']=='out_for_delivery')
                                                <span class="badge badge-soft-warning ml-2 ml-sm-3">
                                                <span class="legend-indicator bg-warning"></span>{{__('messages.out_for_delivery')}}
                                                </span>
                                            @elseif($order['order_status']=='delivered')
                                                <span class="badge badge-soft-success ml-2 ml-sm-3">
                                                <span class="legend-indicator bg-success"></span>{{__('messages.delivered')}}
                                                </span>
                                            @else
                                                <span class="badge badge-soft-danger ml-2 ml-sm-3">
                                                <span class="legend-indicator bg-danger"></span>{{str_replace('_',' ',$order['order_status'])}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-white"
                                           href="{{route('admin.order.details',['id'=>$order['id']])}}"><i
                                                class="tio-visible"></i> {{__('messages.view')}}</a>
                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- End Table -->

                        <!-- Footer -->
                        <div class="card-footer">
                            <!-- Pagination -->
                            <div class="row justify-content-center justify-content-sm-between align-items-sm-center"> 
                                <div class="col-sm-auto">
                                    <div class="d-flex justify-content-center justify-content-sm-end">
                                        <!-- Pagination -->
                                        {!! $orders->links() !!}
                                    </div>
                                </div>
                            </div>
                            <!-- End Pagination -->
                        </div>
                        <!-- End Footer -->
                        <!-- End Card -->
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <!-- Page level plugins -->
    <script>
        // Call the dataTables jQuery plugin
        $(document).ready(function () {
            $('#dataTable').DataTable();
        });
    </script>
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
@endpush
