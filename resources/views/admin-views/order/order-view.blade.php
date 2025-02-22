@extends('layouts.admin.app')

@section('title','Order Details')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link"
                                   href="{{route('admin.orders.list',['status'=>'all'])}}">
                                    Orders
                                </a>
                            </li>
                            <li class="breadcrumb-item active"
                                aria-current="page">{{\App\CentralLogics\translate('order')}} {{\App\CentralLogics\translate('details')}}</li>
                        </ol>
                    </nav>

                    <div class="d-sm-flex align-items-sm-center">
                        <h1 class="page-header-title">{{\App\CentralLogics\translate('order')}} #{{$order['id']}}</h1>

                        @if($order['payment_status']=='paid')
                            <span class="badge badge-soft-success ml-sm-3">
                                <span class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('paid')}}
                            </span>
                        @else
                            <span class="badge badge-soft-danger ml-sm-3">
                                <span class="legend-indicator bg-danger"></span>{{\App\CentralLogics\translate('unpaid')}}
                            </span>
                        @endif

                        @if($order['order_status']=='pending')
                            <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                              <span class="legend-indicator bg-info text"></span>{{\App\CentralLogics\translate('pending')}}
                            </span>
                        @elseif($order['order_status']=='confirmed')
                            <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                              <span class="legend-indicator bg-info"></span>{{\App\CentralLogics\translate('confirmed')}}
                            </span>
                        @elseif($order['order_status']=='processing')
                            <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                              <span class="legend-indicator bg-warning"></span>{{\App\CentralLogics\translate('processing')}}
                            </span>
                        @elseif($order['order_status']=='out_for_delivery')
                            <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                              <span class="legend-indicator bg-warning"></span>{{\App\CentralLogics\translate('out_for_delivery')}}
                            </span>
                        @elseif($order['order_status']=='delivered')
                            <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                              <span class="legend-indicator bg-success"></span>{{\App\CentralLogics\translate('delivered')}}
                            </span>
                        @else
                            <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                              <span class="legend-indicator bg-danger"></span>{{str_replace('_',' ',$order['order_status'])}}
                            </span>
                        @endif
                        <span class="ml-2 ml-sm-3">
                        <i class="tio-date-range"></i> {{date('d M Y h:i a',strtotime($order['created_at']))}}
                </span>

                    </div>

                    <div class="mt-2">
                        <a class="text-body mr-3" target="_blank"
                           href={{route('admin.orders.generate-invoice',[$order['id']])}}>
                            <i class="tio-print mr-1"></i> {{\App\CentralLogics\translate('print')}} {{\App\CentralLogics\translate('invoice')}}
                        </a>
						<a class="text-body mr-3" target="_blank"
                           href="<?php echo asset('storage/app/public/order/')."/".$order['invoice_url']; ?>">
                            <i class="tio-print mr-1"></i> Order PDF
                        </a>
                        <!-- Unfold -->
                        @if($order['order_type']!='self_pickup' && $order['order_type'] != 'pos')
                            <div class="hs-unfold">
                                <select class="form-control" name="delivery_man_id"
                                        onchange="addDeliveryMan(this.value)">
                                    <option
                                        value="0">{{\App\CentralLogics\translate('select')}} {{\App\CentralLogics\translate('deliveryman')}}</option>
                                    @foreach(\App\Model\DeliveryMan::whereIn('branch_id',['all',$order['branch_id']])->where('status', 1)->where('is_available', 1)->get() as $deliveryMan)
                                        <option
                                            value="{{$deliveryMan['id']}}" {{$order['delivery_man_id']==$deliveryMan['id']?'selected':''}}>
                                            {{$deliveryMan['f_name'].' '.$deliveryMan['l_name']}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="hs-unfold ml-1">
                                @if($order['order_status']=='out_for_delivery')
                                    @php($origin=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->first())
                                    @php($current=\App\Model\DeliveryHistory::where(['deliveryman_id'=>$order['delivery_man_id'],'order_id'=>$order['id']])->latest()->first())
                                    @if(isset($origin))
                                        {{--<a class="btn btn-outline-primary" target="_blank"
                                           title="Delivery Boy Last Location" data-toggle="tooltip" data-placement="top"
                                           href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$location['latitude']}}+{{$location['longitude']}}">
                                            <i class="tio-map"></i>
                                        </a>--}}
                                        <a class="btn btn-outline-primary" target="_blank"
                                           title="Delivery Boy Last Location" data-toggle="tooltip" data-placement="top"
                                           href="https://www.google.com/maps/dir/?api=1&origin={{$origin['latitude']}},{{$origin['longitude']}}&destination={{$current['latitude']}},{{$current['longitude']}}">
                                            <i class="tio-map"></i>
                                        </a>
                                    @else
                                        <a class="btn btn-outline-primary" href="javascript:" data-toggle="tooltip"
                                           data-placement="top" title="Waiting for location...">
                                            <i class="tio-map"></i>
                                        </a>
                                    @endif
                                @else
                                    <a class="btn btn-outline-dark" href="javascript:" onclick="last_location_view()"
                                       data-toggle="tooltip" data-placement="top"
                                       title="Only available when order is out for delivery!">
                                        <i class="tio-map"></i>
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="hs-unfold ml-1">
                            <h5>
                                <i class="tio-shop"></i>
                                {{\App\CentralLogics\translate('branch')}} : <label
                                    class="badge badge-secondary">{{$order->branch?$order->branch->name:'Branch deleted!'}}</label>
                            </h5>
                        </div>


                        <div class="hs-unfold float-right">
                            @if($order['order_type'] != 'pos')
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        {{\App\CentralLogics\translate('status')}}
                                    </button>
                                    <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'pending'])}}','Change status to pending ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('pending')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'confirmed'])}}','Change status to confirmed ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('confirmed')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'processing'])}}','Change status to processing ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('processing')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'out_for_delivery'])}}','Change status to out for delivery ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('out_for_delivery')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'delivered'])}}','Change status to delivered ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('delivered')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'returned'])}}','Change status to returned ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('returned')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'failed'])}}','Change status to failed ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('failed')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.status',['id'=>$order['id'],'order_status'=>'canceled'])}}','Change status to canceled ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('canceled')}}</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="hs-unfold float-right pr-2">
                            @if($order['order_type'] != 'pos')
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        {{\App\CentralLogics\translate('payment')}}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.payment-status',['id'=>$order['id'],'payment_status'=>'paid'])}}','Change status to paid ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('paid')}}</a>
                                        <a class="dropdown-item"
                                           onclick="route_alert('{{route('admin.orders.payment-status',['id'=>$order['id'],'payment_status'=>'unpaid'])}}','Change status to unpaid ?')"
                                           href="javascript:">{{\App\CentralLogics\translate('unpaid')}}</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <!-- End Unfold -->
                    </div>
                    @if($order['order_type'] != 'pos')
                        <div class="mt-2">
                            <div class="hs-unfold">
                                <span>{{\App\CentralLogics\translate('Delivery')}} {{\App\CentralLogics\translate('date')}}:</span>
                            </div>
                            <div class="hs-unfold ml-4">

                                <input type="date" value="{{ $order['delivery_date'] }}" name="from" id="from_date"
                                       data-id="{{ $order['id'] }}"
                                       class="form-control" required>

                            </div>
                            <div class="hs-unfold ml-2">
                                <select class="custom-select custom-select time_slote" name="timeSlot"
                                        data-id="{{$order['id']}}">
                                    <option disabled>
                                        --- {{\App\CentralLogics\translate('select')}} {{\App\CentralLogics\translate('Time Slot')}}
                                        ---
                                    </option>

                                    @foreach(\App\Model\TimeSlot::all() as $timeSlot)


                                        <option
                                            value="{{$timeSlot['id']}}" {{$timeSlot->id == $order->time_slot_id ?'selected':''}}>{{$timeSlot['start_time']}}
                                            - {{$timeSlot['end_time']}}</option>


                                    @endforeach

                                </select>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle mr-1"
                       href="{{route('admin.orders.details',[$order['id']-1])}}"
                       data-toggle="tooltip" data-placement="top" title="Previous order">
                        <i class="tio-arrow-backward"></i>
                    </a>
                    <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle"
                       href="{{route('admin.orders.details',[$order['id']+1])}}" data-toggle="tooltip"
                       data-placement="top" title="Next order">
                        <i class="tio-arrow-forward"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-{{$order->customer!=null ? 8 : 12}} mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header" style="display: block!important;">
                        <div class="row">
                            <div class="col-12 pb-2 border-bottom">
                                <h4 class="card-header-title">
                                    {{\App\CentralLogics\translate('order')}} {{\App\CentralLogics\translate('details')}}
                                    <span
                                        class="badge badge-soft-dark rounded-circle ml-1">{{$order->details->count()}}</span>
                                </h4>
                            </div>
                            <div class="col-6 pt-2">
                                <h6 style="color: #8a8a8a;">
                                    {{\App\CentralLogics\translate('order')}} {{\App\CentralLogics\translate('note')}}
                                    : {{$order['order_note']}}
                                </h6>
                            </div>
                            <div class="col-6 pt-2">
                                <div class="text-right">
                                    <h6 class="text-capitalize" style="color: #8a8a8a;">
                                        {{\App\CentralLogics\translate('payment')}} {{\App\CentralLogics\translate('method')}}
                                        : {{str_replace('_',' ',$order['payment_method'])}}
                                    </h6>
                                    <h6 class="" style="color: #8a8a8a;">
                                        @if($order['transaction_reference']==null && $order['order_type']!='pos')
                                            {{\App\CentralLogics\translate('reference')}} {{\App\CentralLogics\translate('code')}}
                                            :
                                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                                    data-target=".bd-example-modal-sm">
                                                {{\App\CentralLogics\translate('add')}}
                                            </button>
                                        @elseif($order['order_type']!='pos')
                                            {{\App\CentralLogics\translate('reference')}} {{\App\CentralLogics\translate('code')}}
                                            : {{$order['transaction_reference']}}
                                        @endif
                                    </h6>
                                    <h6 class="text-capitalize"
                                        style="color: #8a8a8a;">{{\App\CentralLogics\translate('order')}} {{\App\CentralLogics\translate('type')}} : 
                                        <!-- : <label style="font-size: 10px"
                                                 class="badge badge-soft-primary">{{str_replace('_',' ',$order['order_type'])}}</label> -->
                                        <?php if($order['order_type'] == 4){
                                            echo "Scan & Pay";
                                        }
                                        foreach($deliveryOptions as $delOptions){
                                            $delOtopnId = $delOptions->id;
                                            $delOtopnText = $delOptions->text;
                                            if($delOtopnId == $order['order_type']){
                                                echo $delOtopnText;
                                            }
                                        } ?>
                                    </h6>
                                    <h6 class="text-capitalize" style="color: #8a8a8a;">Same Day Delievery : 
                                        <?php if($order['same_day_delievery'] == 1){
                                            echo "Yes";
                                        } else {
                                            echo "No";
                                        } ?>
                                    </h6>
                                    <h6 class="text-capitalize" style="color: #8a8a8a;">Coupon Code : 
                                        <?php if($order['coupon_code'] != NULL && $order['coupon_code'] != ""){
                                            echo $order['coupon_code'];
                                        } ?>
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                    @php($sub_total=0)
                    @php($total_tax=0)
                    @php($total_dis_on_pro=0)
                    @foreach($order->details as $detail)
                        @if($detail->product)
                            <!-- Media -->
                                <div class="media">
                                    <div class="avatar avatar-xl mr-3">
                                        <img class="img-fluid"
                                             src="{{asset('storage/app/public/product')}}/{{json_decode($detail->product['image'],true)[0]}}"
                                             onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                                             alt="Image Description">
                                    </div>

                                    <div class="media-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3 mb-md-0">
                                                <strong> {{$detail->product['name']}}</strong><br>
                                                @if(count(json_decode($detail['variation'],true))>0)
                                                    <strong><u>{{\App\CentralLogics\translate('variation')}}
                                                            : </u></strong>
                                                    @foreach(json_decode($detail['variation'],true)[0] as $key1 =>$variation)
                                                        <div class="font-size-sm text-body">
                                                            <span>{{$key1}} :  </span>
                                                            <span class="font-weight-bold">{{$variation}}</span>
                                                        </div>
                                                    @endforeach
                                                @endif

                                            </div>

                                            <div class="col col-md-4 align-self-center">
				
                                                <!-- <h6><s>{{$detail['product_org_price']." ".\App\CentralLogics\Helpers::currency_symbol()}}</s> - {{$detail['price'] ." ".\App\CentralLogics\Helpers::currency_symbol() ."  ( -".$detail['discount_on_product']." ".\App\CentralLogics\Helpers::currency_symbol()." Discount)"}}</h6> -->
                                              <h6><s>{{\App\CentralLogics\Helpers::currency_symbol()." ".$detail['product_org_price']}}</s> - {{\App\CentralLogics\Helpers::currency_symbol()." ".$detail['price'] ."  (".\App\CentralLogics\Helpers::currency_symbol()." -".$detail['discount_on_product']." Discount)"}}</h6>
                                            </div>
                                            <div class="col col-md-1 align-self-center">
                                                <h5>{{$detail['quantity']}} </h5>
                                            </div>

                                            <!-- <div class="col col-md-1 align-self-center">
                                                <h5>{{$detail->product['capacity']}} {{$detail['unit']}}</h5>
                                            </div> -->


                                            <div class="col col-md-3 align-self-center text-right">
                                                <!-- @php($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity']) -->
                                                @php($amount=$detail['price']*$detail['quantity'])
                                                <h5>{{\App\CentralLogics\Helpers::currency_symbol()." ".$amount}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @php($sub_total+=$amount)
                            @php($total_tax+=$detail['tax_amount']*$detail['quantity'])
                            <!-- End Media -->
                                <hr>
                            @endif
                        @endforeach

                        <!-- <div class="row justify-content-md-end mb-3">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-sm-right">
                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('items')}} {{\App\CentralLogics\translate('price')}}
                                        :
                                    </dt>
                                    <dd class="col-sm-6">{{$sub_total." ".\App\CentralLogics\Helpers::currency_symbol()}}</dd>
                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('tax')}}
                                        / {{\App\CentralLogics\translate('vat')}}:
                                    </dt>
                                    <dd class="col-sm-6">{{$total_tax." ".\App\CentralLogics\Helpers::currency_symbol()}}</dd>

                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('subtotal')}}:</dt>
                                    <dd class="col-sm-6">
                                        {{$sub_total+$total_tax." ".\App\CentralLogics\Helpers::currency_symbol()}}</dd>
                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('coupon')}} {{\App\CentralLogics\translate('discount')}}
                                        :
                                    </dt>
                                    <dd class="col-sm-6">
                                        - {{$order['coupon_discount_amount']." ".\App\CentralLogics\Helpers::currency_symbol()}}</dd>
                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('delivery')}} {{\App\CentralLogics\translate('fee')}}
                                        :
                                    </dt>
                                    <dd class="col-sm-6">
                                        @if($order['order_type']=='self_pickup')
                                            @php($del_c=0)
                                        @else
                                            @php($del_c=$order['delivery_charge'])
                                        @endif
                                        {{$del_c." ".\App\CentralLogics\Helpers::currency_symbol()}}
                                        <hr>
                                    </dd>

                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('total')}}:</dt>
                                    <dd class="col-sm-6">{{$sub_total+$del_c+$total_tax-$order['coupon_discount_amount']." ".\App\CentralLogics\Helpers::currency_symbol()}}</dd>
                                </dl>
                            </div>
                        </div> -->
                        <?php //echo '<pre />'; print_r($cartData); ?>
                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-sm-right">
                                    <dt class="col-sm-6">Item Price:</dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()." ".$cartData->basic_amount}}</dd>
                                    <!-- <dt class="col-sm-6">Item Discount:</dt> <dd class="col-sm-6">{{$cartData->basic_amount - $cartData->total_amount}}</dd> -->
                                    <dt class="col-sm-6">Item Discount:</dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()}} -{{$cartData->product_base_discount}}</dd>
                                    <dt class="col-sm-6">Coupon Discount: </dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()}} -{{$cartData->coupon_discount}}</dd>
                                    <?php if($cartData->coupon_code != ""){ ?>
                                        <dt class="col-sm-6">Coupon Code:</dt> <dd class="col-sm-6">{{$cartData->coupon_code}}</dd>
                                    <?php } ?>
                                    <dt class="col-sm-6">Item Total:</dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()." ".$cartData->total_amount}}</dd>
                                    <dt class="col-sm-6">Tax:</dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()." ".$cartData->tax_amount}}</dd>
                                    <dt class="col-sm-6">Delivery Charge:</dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()." ".$cartData->delivery_charge}}</dd>
                                    <dt class="col-sm-6">Wallet Deduction: </dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()}} -{{$cartData->wallet_balance}}</dd>
                                    <dt class="col-sm-6">Sub Total:</dt> <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()." ".$cartData->remaining_sub_total}}<hr /></dd>
                                    <dt class="col-sm-6">{{\App\CentralLogics\translate('total')}}:</dt>
                                    <dd class="col-sm-6">{{\App\CentralLogics\Helpers::currency_symbol()}}{{$cartData->final_amount}}</dd>
                                </dl>
                            </div>
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            @if($order->customer)
            <div class="col-lg-4">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">{{\App\CentralLogics\translate('customer')}}</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                        <div class="card-body">
                            <div class="media align-items-center" href="javascript:">
                                <div class="avatar avatar-circle mr-3">
                                    <img
                                        class="avatar-img" style="width: 75px"
                                        onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                        src="{{asset('storage/app/public/profile/'.$order->customer->image)}}"
                                        alt="Image Description">
                                </div>
                                <div class="media-body">
                                <span
                                    class="text-body text-hover-primary">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</span>
                                </div>
                                <div class="media-body text-right">
                                    {{--<i class="tio-chevron-right text-body"></i>--}}
                                </div>
                            </div>

                            <hr>

                            <div class="media align-items-center" href="javascript:">
                                <div class="icon icon-soft-info icon-circle mr-3">
                                    <i class="tio-shopping-basket-outlined"></i>
                                </div>
                                <div class="media-body">
                                    <span class="text-body text-hover-primary">{{\App\Model\Order::where('user_id',$order['user_id'])->count()}} orders</span>
                                </div>
                                <div class="media-body text-right">
                                    {{--<i class="tio-chevron-right text-body"></i>--}}
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <h5>{{\App\CentralLogics\translate('contact')}} {{\App\CentralLogics\translate('info')}}</h5>
                            </div>

                            <ul class="list-unstyled list-unstyled-py-2">
                                <li>
                                    <i class="tio-online mr-2"></i>
                                    {{$order->customer['email']}}
                                </li>
                                <li>
                                    <i class="tio-android-phone-vs mr-2"></i>
                                    {{$order->customer['phone']}}
                                </li>
                            </ul>

                            @if($order['order_type']!='self_pickup')
                                <hr>
                                @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{\App\CentralLogics\translate('delivery')}} {{\App\CentralLogics\translate('address')}}</h5>
                                    @if(isset($address))
                                        <a class="link" data-toggle="modal" data-target="#shipping-address-modal"
                                           href="javascript:">{{\App\CentralLogics\translate('edit')}}</a>
                                    @endif
                                </div>
                                @if(isset($address))
                                    <span class="d-block">
                                    {{$address['contact_person_name']}}<br>
                                    {{$address['contact_person_number']}}<br>
                                    {{$address['address_type']}}<br>
                                    <a target="_blank"
                                       href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}">
                                       <i class="tio-map"></i> {{$address['address']}}<br>
                                    </a>
                                </span>
                                @endif
                            @endif
                        </div>
                <!-- End Body -->
                </div>
                <!-- End Card -->

                @if(isset($orderReview) && !empty($orderReview))
                    <br />
                    <div class="card">
                        <!-- Header -->
                        <div class="card-header">
                            Order Review
                        </div>
                            <div class="card-body">
                                <b>Rating:</b> {{$orderReview->rating}}
                                <br />
                                <b>Comment:</b> {{$orderReview->comment}}
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            @endif
        </div>
        <!-- End Row -->
      
      	
      	<br /><br /><br />
        <div class="card mb-3 mb-lg-5">
            <div class="card-header" style="display: block!important;">
                <h4 class="card-header-title">Order History</h4>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-1 border-bottom"><b>Sr.</b></div>
                <div class="col-2 border-bottom"><b>User Type</b></div>
                <div class="col-2 border-bottom"><b>Status</b></div>
                <div class="col-2 border-bottom"><b>Status Reason</b></div>
                <div class="col-1 border-bottom"><b>Collected Amount</b></div>
                <div class="col-2 border-bottom"><b>Signature</b></div>
                <div class="col-2 border-bottom"><b>Date</b></div>
            </div>
        </div>
        
        <?php if(isset($orderHistories) && !empty($orderHistories) && !empty($orderHistories[0])){
            $i = 1;
            foreach($orderHistories as $history){ ?>
                <div class="row">
                    <div class="col-1 border-bottom">{{$i}}</div>
                    <div class="col-2 border-bottom">
                        <?php if($history->user_type == "delivery_man"){
                            echo "Delivery Man";
                        } elseif($history->user_type == "user"){
                            echo "Customer";
                        } elseif($history->user_type == "admin"){
                            echo "Admin";
                        } else {
                            echo $history->user_type;
                        } ?>
                    </div>
                    <div class="col-2 border-bottom">
                        <?php if($history->status_captured == "out_for_delivery"){
                            echo "Out For Delivery";
                        } elseif($history->status_captured == "created"){
                            echo "Created";
                        } elseif($history->status_captured == "pending"){
                            echo "Pending";
                        } elseif($history->status_captured == "delivered"){
                            echo "Delivered";
                        } elseif($history->status_captured == "confirmed"){
                            echo "Confirmed";
                        } elseif($history->status_captured == "processing"){
                            echo "Processing";
                        } else {
                            echo $history->status_captured;
                        } ?>
                    </div>
                    <div class="col-2 border-bottom">{{$history->status_reason}}</div>
                    <div class="col-1 border-bottom">{{$history->collected_amount}}</div>
                    <div class="col-2 border-bottom">
                    
                        <?php if($history->signature != NULL){ ?>
                            <img src="<?php echo asset('storage/app/public/order/')."/".$history->signature; ?>" width="50px" />
                        <?php } ?>
                    </div>
                    <div class="col-2 border-bottom">{{$history->created_at}}</div>
                </div>
            <?php $i++;
            }
        } ?>	
      
      
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4"
                        id="mySmallModalLabel">{{\App\CentralLogics\translate('reference')}} {{\App\CentralLogics\translate('code')}} {{\App\CentralLogics\translate('add')}}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{route('admin.orders.add-payment-ref-code',[$order['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Input Group -->
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="EX : Code123" required>
                        </div>
                        <!-- End Input Group -->
                        <button class="btn btn-primary">{{\App\CentralLogics\translate('submit')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- Modal -->
    <div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-top-cover bg-dark text-center">
                    <figure class="position-absolute right-0 bottom-0 left-0" style="margin-bottom: -1px;">
                        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                             viewBox="0 0 1920 100.1">
                            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z"/>
                        </svg>
                    </figure>

                    <div class="modal-close">
                        <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal"
                                aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor"
                                      d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- End Header -->

                <div class="modal-top-cover-icon">
                    <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
                      <i class="tio-location-search"></i>
                    </span>
                </div>

                @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
                @if(isset($address))
                    <form action="{{route('admin.order.update-shipping',[$order['delivery_address_id']])}}"
                          method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{\App\CentralLogics\translate('type')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address_type"
                                           value="{{$address['address_type']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{\App\CentralLogics\translate('contact')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_number"
                                           value="{{$address['contact_person_number']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{\App\CentralLogics\translate('name')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_name"
                                           value="{{$address['contact_person_name']}}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{\App\CentralLogics\translate('address')}}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address"
                                           value="{{$address['address']}}"
                                           required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{\App\CentralLogics\translate('latitude')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="latitude"
                                           value="{{$address['latitude']}}"
                                           required>
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{\App\CentralLogics\translate('longitude')}}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="longitude"
                                           value="{{$address['longitude']}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white"
                                    data-dismiss="modal">{{\App\CentralLogics\translate('close')}}</button>
                            <button type="submit"
                                    class="btn btn-primary">{{\App\CentralLogics\translate('save')}} {{\App\CentralLogics\translate('changes')}}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>



    </div>
    <!-- End Modal -->
@endsection

@push('script_2')
    <script>
        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/admin/orders/add-delivery-man/{{$order['id']}}/' + id,
                data: $('#product_form').serialize(),
                success: function (data) {
                    //console.log(data);
                    if(data.status == true) {
                        toastr.success('Deliveryman successfully assigned/changed', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else{
                        toastr.error('Deliveryman man can not assign/change in that status', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                },
                error: function () {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function last_location_view() {
            toastr.warning('Only available when order is out for delivery!', {
                CloseButton: true,
                ProgressBar: true
            });
        }
    </script>
    <script>
        $(document).on('change', '#from_date', function () {
            var id = $(this).attr("data-id");
            var value = $(this).val();
            Swal.fire({
                title: 'Are you sure Change this Delivery date?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#01684b',
                cancelButtonColor: 'secondary',
                confirmButtonText: 'Yes, Change it!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.post({
                        url: "{{route('admin.order.update-deliveryDate')}}",

                        data: {
                            "id": id,
                            "deliveryDate": value
                        },

                        success: function (data) {
                            console.log(data);
                            toastr.success('Delivery Date Change successfully');
                            location.reload();
                        }
                    });
                }
            })
        });
        $(document).on('change', '.time_slote', function () {
            var id = $(this).attr("data-id");
            var value = $(this).val();
            Swal.fire({
                title: 'Are you sure Change this?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#01684b',
                cancelButtonColor: 'secondary',
                confirmButtonText: 'Yes, Change it!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.post({
                        url: "{{route('admin.order.update-timeSlot')}}",

                        data: {
                            "id": id,
                            "timeSlot": value
                        },

                        success: function (data) {
                            console.log(data);
                            toastr.success('Time Slot Change successfully');
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
@endpush