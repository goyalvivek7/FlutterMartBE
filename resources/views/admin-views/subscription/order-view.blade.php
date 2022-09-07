@extends('layouts.admin.app')

@section('title','Subscription Detail Details')

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
                                   href="{{route('admin.subscription.list',['status'=>'all'])}}">
                                   Subscription
                                </a>
                            </li>
                            <li class="breadcrumb-item active"
                                aria-current="page">Subscription Details</li>
                        </ol>
                    </nav>

                    <div class="d-sm-flex align-items-sm-center">
                        <h1 class="page-header-title">Subscription #{{$order['id']}}</h1>

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
                        
                        <b>Order Type:</b> {{$order['order_type']}}


                        <div class="hs-unfold float-right">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                    {{\App\CentralLogics\translate('status')}}
                                </button>
                                <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item"
                                        onclick="route_alert('{{route('admin.subscription.status',['id'=>$order['id'],'order_status'=>'pending'])}}','Change status to pending ?')"
                                        href="javascript:">{{\App\CentralLogics\translate('pending')}}</a>
                                    <a class="dropdown-item"
                                        onclick="route_alert('{{route('admin.subscription.status',['id'=>$order['id'],'order_status'=>'confirmed'])}}','Change status to confirmed ?')"
                                        href="javascript:">{{\App\CentralLogics\translate('confirmed')}}</a>
                                    <a class="dropdown-item"
                                        onclick="route_alert('{{route('admin.subscription.status',['id'=>$order['id'],'order_status'=>'ongoing'])}}','Change status to ongoing ?')"
                                        href="javascript:">{{\App\CentralLogics\translate('ongoing')}}</a>
                                    <a class="dropdown-item"
                                        onclick="route_alert('{{route('admin.subscription.status',['id'=>$order['id'],'order_status'=>'completed'])}}','Change status to completed ?')"
                                        href="javascript:">{{\App\CentralLogics\translate('completed')}}</a>
                                    <a class="dropdown-item"
                                        onclick="route_alert('{{route('admin.subscription.status',['id'=>$order['id'],'order_status'=>'canceled'])}}','Change status to canceled ?')"
                                        href="javascript:">{{\App\CentralLogics\translate('canceled')}}</a>
                                </div>
                            </div>
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
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">

            <div class="col-lg-9">

                <?php if(isset($product) && !empty($product)){ ?>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-header-title">Product Description</h4>
                        </div>
                        <div class="card-body">
                            <div class="media">
                                <div class="avatar avatar-xl mr-3">
                                    <?php if(isset($product['image']) && $product['image'] != ""){ ?>
                                        <img class="img-fluid" src="{{asset('storage/app/public/product')}}/{{json_decode($product['image'],true)[0]}}"
                                                onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                                                alt="Image Description">
                                    <?php } ?>
                                </div>

                                <div class="media-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-4 mb-md-0">
                                            <strong> {{$product['name']}}</strong><br>
                                        </div>
                                        <div class="col-md-4 mb-4 mb-md-0">
                                            <strong>Order Time Balance:</strong> {{$order['user_balance']}}<br>
                                            <?php if(!empty($wallet)){ ?>
                                                <strong>Current Wallet Balance:</strong> {{$wallet->balance}}<br>
                                            <?php } else { ?>
                                                <strong>Wallet Balance:</strong> Wallet Not Created<br>
                                            <?php } ?>
                                        </div>
                                        <div class="col-md-4 mb-4 mb-md-0">
                                            <strong>Delivery Address</strong><br />
                                            <?php if(isset($delivery_address)){?>
                                                {{$order->delivery_address['address']}}
                                                <br />
                                                {{$order->delivery_address['contact_person_name']}}
                                                <br />
                                                {{$order->delivery_address['contact_person_number']}}
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-header-title">Subscription Data</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2"><b>Date</b></div>
                                <div class="col-md-1"><b>Price</b></div>
                                <div class="col-md-1"><b>Quantity</b></div>
                                <div class="col-md-2"><b>Payment Status</b></div>
                                <div class="col-md-2"><b>Delivery Status</b></div>
                                <div class="col-md-4"><b>Delivery Man</b></div>
                            </div>
                            <hr />
                            <?php $orderHistory = json_decode($order['order_history']);
                            foreach($orderHistory as $history){?>
                                <div class="row">
                                    <div class="col-md-2">{{$history->date}}</div>
                                    <div class="col-md-1">{{$history->price}}</div>
                                    <div class="col-md-1">{{$history->quantity}}</div>
                                    <div class="col-md-2">
                                        <?php if(isset($history->payment_status) && $history->payment_status == "pending"){ ?>
                                            <a style="color:red;" onclick="route_alert('{{route('admin.subscription.payment-status',['id'=>$order['id'], 'order_status'=>'deduct', 'subs_date'=>$history->date])}}','Are you want to deduct money from wallet?')" href="javascript:">
                                                {{$history->payment_status}}
                                            </a>
                                        <?php } else { ?>
                                            <span style="color:green;">{{$history->payment_status}}</span>
                                        <?php } ?>
                                        
                                    </div>
                                    <div class="col-md-2">{{$history->delivery_status}}</div>
                                    <div class="col-md-4">
                                        <?php if($history->delivery_man != ""){ ?>
                                            <div class="hs-unfold">
                                                <select style="border: 1px solid green;" class="form-control" name="delivery_man_id" onchange="addDeliveryMan(this.value, <?php echo strtotime($history->date); ?>)">
                                                    <option value="">Select Deliveryman</option>
                                                    @foreach(\App\Model\DeliveryMan::whereIn('branch_id',['all',$order['branch_id']])->where('status', 1)->where('is_available', 1)->get() as $deliveryMan)
                                                        <option
                                                            value="{{$deliveryMan['id']}}" {{$history->delivery_man==$deliveryMan['id']?'selected':''}}>
                                                            {{$deliveryMan['f_name'].' '.$deliveryMan['l_name']}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        <?php } else { ?>
                                            <!-- <span style="color:red;">Delivery Man Not Assigned</span> -->
                                            <div class="hs-unfold">
                                                <select style="border: 1px solid red;" class="form-control" name="delivery_man_id" onchange="addDeliveryMan(this.value, <?php echo strtotime($history->date); ?>)">
                                                    <option value="">Select Deliveryman</option>
                                                    @foreach(\App\Model\DeliveryMan::whereIn('branch_id',['all',$order['branch_id']])->where('status', 1)->where('is_available', 1)->get() as $deliveryMan)
                                                        <option
                                                            value="{{$deliveryMan['id']}}" {{$history->delivery_man==$deliveryMan['id']?'selected':''}}>
                                                            {{$deliveryMan['f_name'].' '.$deliveryMan['l_name']}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        <?php }  ?>
                                    </div>
                                </div>
                                <hr />
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Wallet Histories</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2"><b>#</b></div>
                            <div class="col-md-4"><b>Date</b></div>
                            <div class="col-md-3"><b>Amount</b></div>
                            <div class="col-md-3"><b>Subscription Date</b></div>
                        </div>
                        <hr />
                        <?php if(isset($walletHistories) && !empty($walletHistories) && !empty($walletHistories[0])){
                            $i=1;
                            foreach($walletHistories as $walletHistory){?>
                                <div class="row">
                                    <div class="col-md-2">{{$i}}</div>
                                    <div class="col-md-4">{{$walletHistory->created_at}}</div>
                                    <div class="col-md-3">{{$walletHistory->amount}}</div>
                                    <div class="col-md-3">{{$walletHistory->subscription_date}}</div>
                                </div>
                                <?php $i++;
                            }    
                        } else { ?>
                            <div class="row">
                                <div class="col-md-12">No wallet histroy to show</div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                            
                                    

            </div>

            @if($order->customer)
            <div class="col-lg-3">
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
            </div>
            @endif
        </div>
        <!-- End Row -->
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
        function addDeliveryMan(id, subs_date) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/admin/subscription/add-delivery-man/{{$order['id']}}/' + subs_date + '/' + id,
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
