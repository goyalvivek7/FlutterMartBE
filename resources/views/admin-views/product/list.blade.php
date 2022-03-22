@extends('layouts.admin.app')

@section('title','Product List')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 23px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 15px;
            width: 15px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #01684B;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #01684B;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        #banner-image-modal .modal-content {
            width: 1116px !important;
            margin-left: -264px !important;
        }

        @media (max-width: 768px) {
            #banner-image-modal .modal-content {
                width: 698px !important;
                margin-left: -75px !important;
            }


        }

        @media (max-width: 375px) {
            #banner-image-modal .modal-content {
                width: 367px !important;
                margin-left: 0 !important;
            }

        }

        @media (max-width: 500px) {
            #banner-image-modal .modal-content {
                width: 400px !important;
                margin-left: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-filter-list"></i> {{\App\CentralLogics\translate('product')}} {{\App\CentralLogics\translate('list')}} <span style="color: red;">({{ $products->total() }})</span></h1>
                </div>
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="sync_new_products btn btn-primary float-right"><i class="tio-add-circle"></i> Sync New Products</div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header" style="padding-right: 0!important;">
                        <div class="container row">
                            <div class="col-lg-6 mb-3 mb-lg-0">
                                <form action="{{url()->current()}}" method="GET">
                                    <!-- Search -->
                                    <div class="input-group input-group-merge input-group-flush">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="search" class="form-control"
                                               placeholder="Search" aria-label="Search" value="{{$search}}" required>
                                        <button type="submit" class="btn btn-primary">search</button>

                                    </div>
                                    <!-- End Search -->
                                </form>
                            </div>
                            <div class="col-md-6 mb-3 mb-lg-0">
                                <a href="{{route('admin.product.add-new')}}" class="btn btn-primary float-right"><i
                                        class="tio-add-circle"></i>
                                    {{\App\CentralLogics\translate('add')}} {{\App\CentralLogics\translate('new')}} {{\App\CentralLogics\translate('product')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CentralLogics\translate('#')}}</th>
                                <th style="width: 30%">{{\App\CentralLogics\translate('name')}}</th>
                                <th style="width: 25%">{{\App\CentralLogics\translate('image')}}</th>
                                <th>{{\App\CentralLogics\translate('status')}}</th>
                                <th>{{\App\CentralLogics\translate('daily_needs')}}</th>
                                <th>{{\App\CentralLogics\translate('price')}}</th>
                                <th>{{\App\CentralLogics\translate('stock')}}</th>
                                <th>{{\App\CentralLogics\translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($products as $key=>$product)
                                <tr>
                                    <td>{{$products->firstItem()+$key}}</td>
                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                             <a href="{{route('admin.product.view',[$product['id']])}}">
                                               {{substr($product['name'],0,20)}}{{strlen($product['name'])>20?'...':''}}
                                             </a>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="height: 100px; width: 100px; overflow-x: hidden;overflow-y: hidden">
                                            <?php if(isset($product['image']) && !empty($product['image']) && $product['image'] != "[]" && $product['image'] != NULL){ ?>    
                                                <img
                                                        src="{{asset('storage/app/public/product')}}/{{json_decode($product['image'],true)[0]}}"
                                                        style="width: 100px"
                                                        onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'">
                                                
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td>
                                        @if($product['status']==1)
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.product.status',[$product['id'],0])}}'">
                                                <span
                                                    class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('active')}}
                                            </div>
                                        @else
                                            <div style="padding: 10px;border: 1px solid;cursor: pointer"
                                                 onclick="location.href='{{route('admin.product.status',[$product['id'],1])}}'">
                                                <span
                                                    class="legend-indicator bg-danger"></span>{{\App\CentralLogics\translate('disabled')}}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="switch ml-2">
                                            <input type="checkbox" class="status"
                                                   onchange="daily_needs('{{$product['id']}}','{{$product->daily_needs==1?0:1}}')"
                                                   id="{{$product['id']}}" {{$product->daily_needs == 1?'checked':''}}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>{{$product['price']." ".\App\CentralLogics\Helpers::currency_symbol()}}</td>
                                    <td>
                                        <label class="badge badge-soft-info">{{$product['total_stock']}}</label>
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
                                                   href="{{route('admin.product.edit',[$product['id']])}}">{{\App\CentralLogics\translate('edit')}}</a>
                                                <a class="dropdown-item" href="javascript:"
                                                   onclick="form_alert('product-{{$product['id']}}','Want to delete this item ?')">{{\App\CentralLogics\translate('delete')}}</a>
                                                <form action="{{route('admin.product.delete',[$product['id']])}}"
                                                      method="post" id="product-{{$product['id']}}">
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
                                <tfoot class="border-top">
                                {!! $products->links() !!}
                                </tfoot>
                            </table>
                        </div>
                        @if(count($products)==0)
                            <div class="text-center p-4">
                                <img class="mb-3" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image Description" style="width: 7rem;">
                                <p class="mb-0">{{ \App\CentralLogics\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $('#search-form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.product.search')}}',
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
        function daily_needs(id, status) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.product.daily-needs')}}",
                method: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function () {
                    toastr.success('Daily need status updated successfully');
                }
            });
        }
        $(document).ready(function(){
            $(".sync_new_products").click(function(){
                var syncVal = confirm("Are your sure to sync products");
                if(syncVal === true){
                    $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{route('admin.product.sync-products')}}",
                    method: 'POST',
                    success: function () {
                        toastr.success('Products successfully update');
                    }
                });
                }
            });
        });
    </script>
@endpush
