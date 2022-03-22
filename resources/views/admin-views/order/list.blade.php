@extends('layouts.admin.app')

@section('title','Order List')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .for-time {
            border: none;
            margin: 0;
            padding: 0;
            width: 53%;
        }

        td {
            padding-left: 0px;
        }

        .for-sep {
            margin-left: -33px;
            margin-right: 4px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center mb-3">
                <div class="col-9">
                    <h1 class="page-header-title">{{\App\CentralLogics\translate('orders')}} <span style="color: red;">({{ $orders->total() }})</span></h1>

                </div>

                <div class="col-3">
                    <!-- Select -->
                    <select class="custom-select custom-select-sm text-capitalize" name="branch"
                            onchange="filter_branch_orders(this.value)">
                        <option disabled>--- {{\App\CentralLogics\translate('select')}} {{\App\CentralLogics\translate('branch')}} ---</option>
                        <option
                            value="0" {{session('branch_filter')==0?'selected':''}}>{{\App\CentralLogics\translate('all')}} {{\App\CentralLogics\translate('branch')}}</option>
                        @foreach(\App\Model\Branch::all() as $branch)
                            <option
                                value="{{$branch['id']}}" {{session('branch_filter')==$branch['id']?'selected':''}}>{{$branch['name']}}</option>
                        @endforeach
                    </select>
                    <!-- End Select -->
                </div>
            </div>

            <!-- End Row -->

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
                        <a class="nav-link active" href="#">{{\App\CentralLogics\translate('order')}} {{\App\CentralLogics\translate('list')}}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content space-between align-items-center flex-grow-1">
{{--                    <div class="col-md-3 flex-start"><h5>Orders Table <span style="color: red;">({{ $orders->total() }})</span></h5></div>--}}
                    <div class="col-md-6 mt-md-0 mt-2">
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
                    <div class="col-md-6 mt-md-0 mt-2">
                        <form action="{{url()->current()}}" method="GET" class="flex-end">
                            <div><input  type="date" name="date" value="{{ $date }}" class="form-control"></div>

                            <div class="mx-2">
                                <select class="custom-select" name="time">
                                    <option value=0 >--- {{\App\CentralLogics\translate('select')}} {{\App\CentralLogics\translate('Time Slot')}}---
                                    </option>
                                    @foreach(\App\Model\TimeSlot::all() as $timeSlot)
                                        <option value="{{$timeSlot['id']}}" {{$timeSlot['id']==$time?'selected':''}} >{{$timeSlot['start_time']}}
                                            - {{$timeSlot['end_time']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" >Filter</button>
                        </form>
                    </div>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       style="width: 100%">
                    <thead class="thead-light">
                    <tr>
                        <th class="">
                            {{\App\CentralLogics\translate('#')}}
                        </th>
                        <th class="table-column-pl-0">{{\App\CentralLogics\translate('order')}}</th>
                        <th>{{\App\CentralLogics\translate('Delivery')}} {{\App\CentralLogics\translate('date')}}</th>
                        <th>{{\App\CentralLogics\translate('Time Slot')}}</th>
                        <th>{{\App\CentralLogics\translate('customer')}}</th>
                        <th>{{\App\CentralLogics\translate('branch')}}</th>


                        {{-- <th>{{\App\CentralLogics\translate('payment')}} {{\App\CentralLogics\translate('status')}}</th> --}}
                        <th>{{\App\CentralLogics\translate('total')}}</th>
                        <th>{{\App\CentralLogics\translate('order')}} {{\App\CentralLogics\translate('status')}}</th>
                        <th>{{\App\CentralLogics\translate('actions')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($orders as $key=>$order)

                        <tr class="status-{{$order['order_status']}} class-all">
                            <td class="">
                                {{$orders->firstItem()+$key}}
                            </td>
                            <td class="table-column-pl-0">
                                <a href="{{route('admin.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                            </td>
                            <td>{{date('d M Y',strtotime($order['delivery_date']))}}</td>
                            <td>
                                <span>{{$order->time_slot?$order->time_slot['start_time'].' - ' .$order->time_slot['end_time'] :'No Time Slot'}}</span>

                            </td>
                            <td>
                                @if($order->customer)
                                    <a class="text-body text-capitalize"
                                       href="{{route('admin.customer.view',[$order['user_id']])}}">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</a>
                                @else
                                    <label
                                        class="badge badge-danger">{{\App\CentralLogics\translate('invalid')}} {{\App\CentralLogics\translate('customer')}} {{\App\CentralLogics\translate('data')}}</label>
                                @endif
                            </td>
                            <td>
                                <label
                                    class="badge badge-soft-primary">{{$order->branch?$order->branch->name:'Branch deleted!'}}</label>
                            </td>

                            <td>{{$order['order_amount'] ." ". \App\CentralLogics\Helpers::currency_symbol()}}</td>
                            <td class="text-capitalize">
                                @if($order['order_status']=='pending')
                                    <span class="badge badge-soft-info ml-2 ml-sm-3">
                                      <span class="legend-indicator bg-info"></span>{{\App\CentralLogics\translate('pending')}}
                                    </span>
                                @elseif($order['order_status']=='confirmed')
                                    <span class="badge badge-soft-info ml-2 ml-sm-3">
                                      <span class="legend-indicator bg-info"></span>{{\App\CentralLogics\translate('confirmed')}}
                                    </span>
                                @elseif($order['order_status']=='processing')
                                    <span class="badge badge-soft-warning ml-2 ml-sm-3">
                                      <span class="legend-indicator bg-warning"></span>{{\App\CentralLogics\translate('processing')}}
                                    </span>
                                @elseif($order['order_status']=='out_for_delivery')
                                    <span class="badge badge-soft-warning ml-2 ml-sm-3">
                                      <span class="legend-indicator bg-warning"></span>{{\App\CentralLogics\translate('out_for_delivery')}}
                                    </span>
                                @elseif($order['order_status']=='delivered')
                                    <span class="badge badge-soft-success ml-2 ml-sm-3">
                                      <span class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('delivered')}}
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger ml-2 ml-sm-3">
                                      <span class="legend-indicator bg-danger"></span>{{str_replace('_',' ',$order['order_status'])}}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        <i class="tio-settings"></i>
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item"
                                           href="{{route('admin.orders.details',['id'=>$order['id']])}}"><i
                                                class="tio-visible"></i> {{\App\CentralLogics\translate('view')}}</a>
                                        <a class="dropdown-item" target="_blank"
                                           href="{{route('admin.orders.generate-invoice',[$order['id']])}}"><i
                                                class="tio-download"></i> {{\App\CentralLogics\translate('invoice')}}</a>
                                    </div>
                                </div>
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
                            {{--<nav id="datatablePagination" aria-label="Activity pagination"></nav>--}}
                        </div>
                    </div>
                </div>
                @if(count($orders)==0)
                    <div class="text-center p-4">
                        <img class="mb-3" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image Description" style="width: 7rem;">
                        <p class="mb-0">{{ \App\CentralLogics\translate('No_data_to_show')}}</p>
                    </div>
                @endif
                <!-- End Pagination -->
            </div>
            <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')

    <script>
        function filter_branch_orders(id) {
            location.href = '{{url('/')}}/admin/orders/branch-filter/' + id;
        }
    </script>

    <script>
        $('#from_date').on('change', function () {
            let dateData = $('#from_date').val();
            console.log(dateData);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.orders.date_search')}}',
                data: {
                    'dateData': dateData,
                },

                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.card-footer').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
        $('#from_time').on('change', function () {
            let timeData = $('#from_time').val();
            let dateData = $('#from_date').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.orders.time_search')}}',
                data: {
                    'timeData': timeData,
                    'dateData': dateData,

                },

                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.card-footer').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
