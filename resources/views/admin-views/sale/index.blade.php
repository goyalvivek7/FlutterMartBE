@extends('layouts.admin.app')

@section('title','Add new sale')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> Add New Sale</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.sale.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Sale Title" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Sub Title</label>
                                <input type="text" name="sub_title" class="form-control" placeholder="Sale Sub Title" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">Flash Tag</label>
                                <input type="text" name="flash_tag" class="form-control" placeholder="Sale Flash Tag" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Sale Icon</label><small>in icon size</small>
                                <div class="custom-file">
                                    <input type="file" name="icon" id="customFileEg1" class="custom-file-input" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                    <label class="custom-file-label" for="customFileEg1">{{\App\CentralLogics\translate('choose')}} {{\App\CentralLogics\translate('file')}}</label>
                                </div>
                                <hr>
                                <center>
                                    <img style="width: 80%;border: 1px solid; border-radius: 10px;" id="viewer"
                                         src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}" alt="banner image"/>
                                </center>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">Sale Type<span class="input-label-secondary">*</span></label>
                                <select name="item_type" class="form-control" onchange="show_item(this.value)">
                                    <option value="products">{{\App\CentralLogics\translate('product')}}</option>
                                    <option value="categories">{{\App\CentralLogics\translate('category')}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group" id="main-category">
                                <label class="input-label" for="exampleFormControlSelect2">Main Category</label>
                                <select name="category_id" id="category_id" class="form-control js-select2-custom" multiple>
                                    @foreach($categories as $category)
                                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group" id="sub-category">
                                <label class="input-label" for="exampleFormControlSelect3">Sub Category</label>
                                <select name="sub_category_id" id="sub_category_id" class="form-control js-select2-custom" multiple>
                                    
                                </select>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group" id="child-category">
                                <label class="input-label" for="exampleFormControlSelect4">Child Category</label>
                                <select name="child_category_id" id="child_category_id" class="form-control js-select2-custom" multiple>
                                    
                                </select>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group" id="main-category">
                                <label class="input-label" for="exampleFormControlSelect1">Products</label>
                                <select name="product_ids" id="product_ids" class="form-control js-select2-custom" multiple>
                                    
                                </select>
                            </div>
                        </div>


                    </div>

                    <!-- <div class="row">
                        
                        <div class="col-6">
                            <div class="form-group" id="type-product">
                                <label class="input-label" for="exampleFormControlSelect1">{{\App\CentralLogics\translate('product')}} <span
                                        class="input-label-secondary">*</span></label>
                                <select name="product_id" class="form-control js-select2-custom">
                                    @foreach($products as $product)
                                        <option value="{{$product['id']}}">{{$product['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" id="type-category" style="display: none">
                                <label class="input-label" for="exampleFormControlSelect1">{{\App\CentralLogics\translate('category')}} <span
                                        class="input-label-secondary">*</span></label>
                                <select name="category_id" class="form-control js-select2-custom">
                                    @foreach($categories as $category)
                                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div> -->

                    <hr>
                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
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

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $("#category_id").change(function(){
            //var categoryId = JSON.stringify( $(this).val() );
            var categoryId = $(this).val();
            //var categoryId = 7;
            console.log("categoryId", categoryId);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.category.subsearch')}}',
                data: JSON.stringify({'search':categoryId}),
                cache: false,
                contentType: false,
                dataType: "json",
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    alert("categoryId alert " + categoryId);
                    console.log("categoryId", data);
                    $("#sub_category_id").append("");
                    $("#sub_category_id").append(data);
                    //$('#set-rows').html(data.view);
                    //$('.card-footer').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $("#sub_category_id").on('change', function(){
            var categoryId = $(this).val();
            //var categoryId = 7;
            alert("categoryId" + categoryId);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.category.childsearch')}}',
                data: {search:categoryId},
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    alert("categoryId alert " + categoryId);
                    console.log("categoryId", data);
                    $("#child_category_id").append("");
                    $("#child_category_id").append(data);
                    //$('#set-rows').html(data.view);
                    //$('.card-footer').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        function show_item(type) {
            if (type === 'product') {
                $("#type-product").show();
                $("#type-category").hide();
            } else {
                $("#type-product").hide();
                $("#type-category").show();
            }
        }
    </script>
@endpush
