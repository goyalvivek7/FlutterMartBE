@extends('layouts.admin.app')

@section('title','Add new sub category')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i
                            class="tio-add-circle-outlined"></i> {{\App\CentralLogics\translate('add')}} {{\App\CentralLogics\translate('new')}} {{\App\CentralLogics\translate('sub_category')}}
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.category.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @php($language=\App\Model\BusinessSetting::where('key','language')->first())
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
                        <div class="row">
                            <div class="col-6">
                                @foreach(json_decode($language) as $lang)
                                    <div class="form-group {{$lang != $default_lang ? 'd-none':''}} lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('sub_category')}} {{\App\CentralLogics\translate('name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" placeholder="New Sub Category" {{$lang == $default_lang? 'required':''}}>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                                @else
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group lang_form" id="{{$default_lang}}-form">
                                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('sub_category')}} {{\App\CentralLogics\translate('name')}}({{strtoupper($lang)}})</label>
                                                <input type="text" name="name[]" class="form-control" placeholder="New Sub Category" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$default_lang}}">
                                            @endif
                                            <input name="position" value="1" style="display: none">
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="input-label"
                                                       for="exampleFormControlSelect1">{{\App\CentralLogics\translate('main')}} {{\App\CentralLogics\translate('category')}}
                                                    <span class="input-label-secondary">*</span></label>
                                                <select id="exampleFormControlSelect1" name="parent_id" class="form-control" required>
                                                    @foreach(\App\Model\Category::where(['position'=>0])->get() as $category)
                                                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6 from_part_1">
                                            <label>{{ \App\CentralLogics\translate('image') }}</label><small style="color: red">* ( {{ \App\CentralLogics\translate('ratio') }}
                                                3:1 )</small>
                                            <div class="custom-file">
                                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
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
                                    <div class="row">
                                        <div class="col-6 from_part_1">
                                            <label>{{ \App\CentralLogics\translate('category_icon') }}</label><small style="color: red"> ( {{ \App\CentralLogics\translate('ratio') }} 64x64px)</small>
                                            <div class="custom-file">
                                                <input type="file" name="cat_icon" id="customFileEg2" class="custom-file-input"
                                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="custom-file-label" for="customFileEg2">{{ \App\CentralLogics\translate('choose') }}
                                                    {{ \App\CentralLogics\translate('file') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-6 from_part_2">
                                            <div class="form-group">
                                                <center>
                                                    <img style="width: 30%;border: 1px solid; border-radius: 10px;" id="viewer2"
                                                            src="{{ asset('public/assets/admin/img/900x400/img1.jpg') }}" alt="image" />
                                                </center>
                                            </div>
                                        </div>
                                    </div>
                                    <hr />

                            </div>
                        </div>
                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                </form>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <hr>
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <h5>Sub Category Table <span style="color: red;">({{ $categories->total() }})</span></h5>
                            </div>
                            <div class="col-md-8 float-right" style="width: 30vw">
                                <form action="{{url()->current()}}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                            placeholder="Search by Sub Category" aria-label="Search" value="{{$search}}" required>
                                        <button type="submit" class="btn btn-primary">search</button>

                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{\App\CentralLogics\translate('#')}}</th>
                                    <th style="width: 15%">{{\App\CentralLogics\translate('main')}} {{\App\CentralLogics\translate('category')}}</th>
                                    <th style="width: 15%">{{\App\CentralLogics\translate('sub_category')}}</th>
                                    <th style="width: 20%">Image</th>
                                    <th style="width: 20%">Category Icon</th>
                                    <th style="width: 20%">{{\App\CentralLogics\translate('status')}}</th>
                                    <th style="width: 10%">{{\App\CentralLogics\translate('action')}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($categories as $key=>$category)
                                <tr>
                                    <td>{{$categories->firstItem()+$key}}</td>
                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                            {{$category->parent['name']}}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                            {{$category['name']}}
                                        </span>
                                    </td>

                                    <td>
                                        @if($category['image'] != "" && $category['image'] != NULL)
                                            <img src="{{asset('storage/app/public/category')}}/{{$category['image']}}" width="100px" />
                                        @else
                                        <img src="{{asset('storage/app/public/category')}}/def.png" width="100px" />
                                        @endif
                                    </td>

                                    <td>
                                        @if($category['cat_icon'] != "" && $category['cat_icon'] != NULL)
                                            <img src="{{asset('storage/app/public/category')}}/{{$category['cat_icon']}}" width="48px" />
                                        @else
                                        <img src="{{asset('storage/app/public/category')}}/def.png" width="48" />
                                        @endif
                                    </td>

                                    <td>
                                        @if($category['status']==1)
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.category.status',[$category['id'],0])}}'">
                                                <span
                                                    class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('active')}}
                                            </div>
                                        @else
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.category.status',[$category['id'],1])}}'">
                                                <span
                                                    class="legend-indicator bg-danger"></span>{{\App\CentralLogics\translate('disabled')}}
                                            </div>
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
                                                   href="{{route('admin.category.edit',[$category['id']])}}">{{\App\CentralLogics\translate('edit')}}</a>
                                                <a class="dropdown-item" href="javascript:"
                                                   onclick="form_alert('category-{{$category['id']}}','Want to delete this category ?')">{{\App\CentralLogics\translate('delete')}}</a>
                                                <form action="{{route('admin.category.delete',[$category['id']])}}"
                                                      method="post" id="category-{{$category['id']}}">
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
                        <div class="page-area">
                            <table>
                                <tfoot>
                                {!! $categories->links() !!}
                                </tfoot>
                            </table>
                        </div>
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
        $('#search-form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.category.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
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
