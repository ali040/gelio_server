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
                    <h1 class="page-header-title">{{__('messages.module')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header"><h5>{{__('messages.add').' '.__('messages.new')}} {{__('messages.module')}}</h5></div>
            <div class="card-body">
                <form action="{{route('admin.module.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = 'en')
                    @if($language)
                        @php($default_lang = json_decode($language)[0])
                        <ul class="nav nav-tabs mb-4">
                            @foreach(json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link {{$lang == $default_lang? 'active':''}}" href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($language)
                        @foreach(json_decode($language) as $lang)
                            <div class="form-group {{$lang != $default_lang ? 'd-none':''}} lang_form" id="{{$lang}}-form">
                                <label class="input-label text-capitalize" for="exampleFormControlInput1">{{__('messages.module')}} {{__('messages.name')}} ({{strtoupper($lang)}})</label>
                                <input type="text" name="module_name[]" class="form-control" maxlength="191" {{$lang == $default_lang? 'required':''}} oninvalid="document.getElementById('en-link').click()">
                            </div>
                            <input type="hidden" name="lang[]" value="{{$lang}}">
                        @endforeach
                    @else
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{__('messages.module')}} {{__('messages.name')}}</label>
                            <input type="text" name="module_name" class="form-control" placeholder="{{__('messages.new_category')}}" value="{{old('name')}}" required maxlength="191">
                        </div>
                        <input type="hidden" name="lang[]" value="{{$lang}}">
                    @endif
                    <!-- <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{__('messages.name')}}</label>
                        <input type="text" name="name" value="{{isset($module)?$module['name']:''}}" class="form-control" placeholder="New Category" required>
                    </div> -->

                    <div class="form-group">
                        <label class="input-label" for="module_type">{{__('messages.module_type')}}</label>
                        <select name="module_type" id="module_type" class="form-control text-capitalize" onchange="modulChange(this.value)">
                        <option disabled selected>{{__('messages.select')}} {{__('messages.module_type')}}</option>
                            @foreach (config('module.module_type') as $key)
                                <option class="" value="{{$key}}">{{$key}}</option>
                            @endforeach
                        </select>
                        <small class="text-danger">{{__('messages.module_type_change_warning')}}</small>
                        <div class="card mt-1" id="module_des_card" style="display: none;">
                            <div class="card-body" id="module_description"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{__('messages.image')}}</label><small style="color: red">* ( {{__('messages.ratio')}} 1:1)</small>
                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                            <label class="custom-file-label" for="customFileEg1">{{__('messages.choose')}} {{__('messages.file')}}</label>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0%;">
                        <center>
                            <img style="width: 200px;border: 1px solid; border-radius: 10px;" id="viewer"
                                src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}"
                                alt="image"/>
                        </center>
                    </div>
                    <div class="form-group pt-2">
                        <button type="submit" class="btn btn-primary">{{__('messages.add')}}</button>
                    </div>
                    
                </form>
            </div>
        </div>

    </div>

@endsection

@push('script_2')
    <script>
        function modulChange(id)
        {
            $.get({
                url: "{{url('/')}}/admin/module/type/?module_type="+id,
                dataType: 'json',
                success: function (data) {
                    $('#module_des_card').show();
                    $('#module_description').html(data.data.description);
                    console.log(data.data.description);
                },
            });
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });
    </script>
    <script>
        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.split("-")[0];
            console.log(lang);
            $("#"+lang+"-form").removeClass('d-none');
            if(lang == '{{$default_lang}}')
            {
                $(".from_part_2").removeClass('d-none');
            }
            else
            {
                $(".from_part_2").addClass('d-none');
            }
        });
    </script>
@endpush
