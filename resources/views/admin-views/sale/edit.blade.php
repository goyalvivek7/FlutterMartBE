@extends('layouts.admin.app')

@section('title','Update banner')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> Edit</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.sale.update',[$banner['id']])}}" method="post"
                      enctype="multipart/form-data">
                    @csrf @method('put')
                    <input type="hidden" name="id" value="{{$banner['id']}}" />
                    <?php $subCatId = explode(',', $banner['sub_cat_id']); ?>
                    <input type="hidden" name="old_sub_cat_id[]" id="old_sub_cat_id" value="<?php echo $banner['sub_cat_id']; ?>" />
                    <input type="hidden" name="old_child_cat_id" id="old_child_cat_id" value="<?php echo $banner['child_cat_id']; ?>" />

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{\App\CentralLogics\translate('title')}}</label>
                                <input type="text" name="title" value="{{$banner['title']}}" class="form-control"
                                       placeholder="New banner" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{\App\CentralLogics\translate('item')}} {{\App\CentralLogics\translate('type')}}<span
                                        class="input-label-secondary">*</span></label>
                                <select name="item_type" id="item_type" class="form-control" onchange="show_item(this.value)">
                                    <option value="">Select</option>
                                    <option value="products" {{$banner['sale_type']=='products'?'selected':''}}>{{\App\CentralLogics\translate('product')}}</option>
                                    <option value="categories" {{$banner['sale_type']=='categories'?'selected':''}}>{{\App\CentralLogics\translate('category')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php //echo '!!!!<pre />'; print_r($banner); ?>
                    <div class="row">
                        <div class="col-6" id="type-category">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">Category</label>
                                <select name="cat_id[]" id="cat_id" class="form-control js-select2-custom" multiple>
                                    <?php $i = 0; ?>
                                    @foreach($categories as $category)
                                        <!-- <option value="{{$category['id']}}" {{$banner['cat_id']==$category['id']?'selected':''}}>{{$category['name']}}</option> -->
                                        <?php if(($i==0 && $banner['cat_id'] != NULL) || ($i==0 && $banner['cat_id'] != "")){
                                            echo '<option value="">Select Category</option>';
                                            $i++;
                                        } ?>
                                        <option value="{{$category['id']}}" <?php if($banner['cat_id'] != NULL && in_array($category['id'], json_decode($banner['cat_id']))){ echo 'selected'; } ?>>{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6" id="type-sub-category">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">Sub Category</label>
                                <select name="sub_category_id[]" id="sub_category_id" class="form-control js-select2-custom" multiple>
                                    @foreach($subCategories as $category)
                                        <?php if(($i==0 && $banner['sub_cat_id'] != NULL) || ($i==0 && $banner['sub_cat_id'] != "")){
                                            echo '<option value="">Select Sub Category</option>';
                                            $i++;
                                        } ?>
                                        <option value="{{$category['id']}}" <?php if($banner['sub_cat_id'] != NULL && in_array($category['id'], json_decode($banner['sub_cat_id']))){ echo 'selected'; } ?>>{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6" id="type-cat-category">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">Child Category</label>
                                <select name="child_cat_id[]" id="child_cat_id" class="form-control js-select2-custom" multiple>
                                    @foreach($childCategories as $category)
                                    <?php if(($i==0 && $banner['child_cat_id'] != NULL) || ($i==0 && $banner['child_cat_id'] != "")){
                                            echo '<option value="">Select Sub Category</option>';
                                            $i++;
                                        } ?>
                                        <option value="{{$category['id']}}" <?php if($banner['child_cat_id'] != NULL && in_array($category['id'], json_decode($banner['child_cat_id']))){ echo 'selected'; } ?>>{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6" id="type-product">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlSelect1">{{\App\CentralLogics\translate('product')}} <span
                                        class="input-label-secondary">*</span></label>
                                <select name="product_id[]" id="product_id" class="form-control js-select2-custom" multiple>
                                    @foreach($products as $product)
                                        <option
                                            value="{{$product['id']}}" <?php if($banner['allow_ids'] != NULL && in_array($product['id'], json_decode($banner['allow_ids']))){ echo 'selected'; } ?>>
                                            {{$product['name']}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary">{{\App\CentralLogics\translate('update')}}</button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>

        function update_product_listing(catIds, catPosition){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var data = {"cat-id":catIds, "cat-position": catPosition}
            $.post({
                url: '{{route('admin.product.get-product-listing')}}',
                dataType : "json",
                contentType: "application/json; charset=utf-8",
                data : JSON.stringify(data),
                success: function (data) {                        
                    console.log("another data", data);
                    var appendString2 = "";
                    var selProductIds = $("#product_id").val();
                    $.each(data, function(index, productArray) {
                        var arrProductId = productArray['id'];
                        var arrCatIdStr = arrProductId.toString();
                        var arrProductName = productArray['name'];
                        if(selProductIds.indexOf(arrCatIdStr) > -1){
                        } else {
                            appendString2 += '<option value="'+arrProductId+'">'+arrProductName+'</option>';
                        }
                    });
                    $("#product_id").append(appendString2);
                    
                }
            });
        }

        function show_item(type) {
            if (type === 'products') {
                $("#type-product").show();
                $("#type-category").hide();
            } else if(type === 'categories') {
                $("#type-product").hide();
                $("#type-category").show();
            }
        }

        function check_sub_cat(catIds, subCatIds, fType){
            if(catIds != ""){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var data = {"parent_id":catIds}
                $.post({
                    url: '{{route('admin.product.get-categories-multi')}}',
                    dataType : "json",
                    contentType: "application/json; charset=utf-8",
                    data : JSON.stringify(data),
                    success: function (data) {
                        var appendString = "";
                        $.each(data, function(index, catArray) { 
                            var arrCatId = catArray['id'];
                            var arrCatIdStr = arrCatId.toString();
                            //alert(arrCatId + "####" + jQuery.inArray("'"+arrCatId+"'", subCatIds));
                            if(subCatIds != ""){
                                //if(jQuery.inArray(arrCatId, subCatIds) > -1){
                                if(subCatIds.indexOf(arrCatIdStr) > -1){
                                } else {
                                    appendString += '<option value="'+catArray['id']+'">'+catArray['name']+'</option>';
                                }
                            } else {
                                appendString += '<option value="'+catArray['id']+'">'+catArray['name']+'</option>';
                            }
                        });

                        if(fType == "sub-cat"){
                            $("#sub_category_id").append(appendString);
                        }
                        if(fType == "child-cat"){
                            $("#child_cat_id").append(appendString);
                        }
                        
                    }
                });
            } else {
                alert("category empty");
            }
        }


        $("#cat_id").on('change', function(){
            var catIds = $("#cat_id").val();
            var subCatIds = $("#sub_category_id").val();
            //var options = $('#sub_category_id option');
            //var options = $('li.select2-results__option span').val();
            // var optionValues = $.map(options ,function(option) {
            //     console.log("option", option);
            //     return option.value;
            // });
            //console.log("subCatIds", subCatIds);
            //console.log("optionValues", optionValues);
            check_sub_cat(catIds, subCatIds, 'sub-cat');
            update_product_listing(catIds, 0);
        });

        $("#sub_category_id").on('change', function(){
            var catIds = $("#sub_category_id").val();
            var subCatIds = $("#child_cat_id").val();
            check_sub_cat(catIds, subCatIds, 'child-cat');
            update_product_listing(catIds, 1);
        });

        $("#child_cat_id").on('change', function(){
            var catIds = $("#child_cat_id").val();
            update_product_listing(catIds, 3);
        });

    </script>
@endpush
