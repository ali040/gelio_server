    <!-- Page Header -->
    <div class="page-header">

        <h1 class="page-header-title text-break">{{$store->name}}</h1>
        
        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next" style="display: none;">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            <ul class="nav nav-tabs page-header-tabs">
            <li class="nav-item">
                    <a class="nav-link {{request('tab')==null?'active':''}}" href="{{route('admin.vendor.view', $store->id)}}">{{__('messages.store')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='order'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'order'])}}"  aria-disabled="true">{{__('messages.order')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='item'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'item'])}}"  aria-disabled="true">{{__('messages.item')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='discount'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'discount'])}}"  aria-disabled="true">{{__('messages.discount')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='settings'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'settings'])}}"  aria-disabled="true">{{__('messages.settings')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='transaction'?'active':''}}" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'transaction'])}}"  aria-disabled="true">{{__('messages.transaction')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{route('admin.vendor.view', ['store'=>$store->id, 'tab'=> 'reviews'])}}"  aria-disabled="true">{{__('messages.reviews')}}</a>
                </li>
            </ul>
            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->
    </div>
    <!-- End Page Header -->