@extends('layouts.admin.app')

@section('title','Smart Deals')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> Add New Smart Deal</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.sale.deal-store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @php($language = \App\Model\BusinessSetting::where('key', 'language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = 'en')
                    @if ($language)
                        @php($default_lang = json_decode($language)[0])
                        <ul class="nav nav-tabs mb-4">
                            @foreach (json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link {{ $lang == $default_lang ? 'active' : '' }}" href="#"
                                       id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="row">
                            
                                @foreach (json_decode($language) as $lang)
                                <div class="col-6">
                                    <div class="form-group {{ $lang != $default_lang ? 'd-none' : '' }} lang_form"
                                         id="{{ $lang }}-form">
                                        <label class="input-label"
                                               for="exampleFormControlInput1">{{ \App\CentralLogics\translate('name') }}
                                            ({{ strtoupper($lang) }})</label>
                                        <input type="text" name="title" class="form-control" placeholder="New Deal Title" {{ $lang == $default_lang ? 'required' : '' }}>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                </div>
                                <div class="col-6 from_part_1">
                                    <label>Status</label>
                                    <div class="custom-file">
                                        <input type="checkbox" name="status" />
                                    </div>
                                </div>
                                @endforeach
                                @else
                                @endif
                                    
                            </div>
                            <div class="row">
                                <div class="col-6 from_part_1">
                                    <label>Image</label><small style="color: red">* ( {{ \App\CentralLogics\translate('ratio') }}
                                        3:1 )</small>
                                    <div class="custom-file">
                                        <input type="file" name="slider_image" id="customFileEg1" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                        <label class="custom-file-label" for="customFileEg1">{{ \App\CentralLogics\translate('choose') }}
                                            {{ \App\CentralLogics\translate('file') }}</label>
                                    </div>
                                </div>
                                <div class="col-6 from_part_2">
                                    <div class="form-group">
                                        <center>
                                            <img style="width: 30%;border: 1px solid; border-radius: 10px;" id="viewer"
                                                    src="{{ asset('public/assets/admin/img/900x400/img1.jpg') }}" alt="image" />
                                        </center>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                </form>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <hr>
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Smart Deals Table <span style="color: red;">({{ $smartDeals->total() }})</span></h5>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CentralLogics\translate('#')}}</th>
                                <th style="width: 30%">{{\App\CentralLogics\translate('name')}}</th>
                                <th style="width: 50%">Image</th>
                                <th style="width: 10%">{{\App\CentralLogics\translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($smartDeals as $key=>$smartDeal)
                                <tr>
                                    <td>{{$smartDeals->firstItem()+$key}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{$smartDeal->title}}
                                    </span>
                                    </td>
                                    <td>
                                        @if($smartDeal->slider_image != "" && $smartDeal->slider_image != NULL)
                                            <img src="{{asset('storage/app/public/smart_deals')}}/{{$smartDeal->slider_image}}" width="100px" />
                                        @else
                                        <img src="{{asset('storage/app/public/category')}}/def.png" width="100px" />
                                        @endif
                                    </td>
                                    <td>
                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button"
                                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                <i class="tio-settings"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item"
                                                   href="{{route('admin.sale.deal-edit',[$smartDeal->id])}}">{{\App\CentralLogics\translate('edit')}}</a>
                                                <a class="dropdown-item" href="javascript:"
                                                   onclick="form_alert('category-{{$smartDeal->id}}','Want to delete this deal')">{{\App\CentralLogics\translate('delete')}}</a>
                                                <form action="{{route('admin.sale.deal-delete',[$smartDeal->id])}}"
                                                      method="post" id="category-{{$smartDeal->id}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </div>
                                        <!-- End Dropdown -->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <hr>

                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
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

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function readURL2(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer2').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });
        $("#customFileEg2").change(function () {
            readURL2(this);
        });
    </script>
@endpush
