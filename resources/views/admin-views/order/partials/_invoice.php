<?php use App\CentralLogics\Helpers as HELPER; ?>
<table style="width: 100%;">
    <tr style="text-align: center;">
        <td colspan="2">
            <h2 class="float-right">#INVOICE</h2>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center;">
            <br><br>
            <img width="150" src="<?php echo asset('storage/app/public/restaurant').'/'.$bisData['logo']; ?>">
            <br><br>
        </td>
    </tr>
    <tr>
        <td>
            <h4>Company Detail:</h4>
            <strong>Phone : <?php echo $bisData['phone']; ?></strong><br>
            <strong>Email : <?php echo $bisData['email_address']; ?></strong><br>
            <strong>Address : <?php echo $bisData['address']; ?></strong><br><br>
        </td>
        <td>
            <h4>Customer Detail:</h4>
            <?php if($order->customer){ ?>
                <strong class="float-right">Order ID : <?php echo $order['id']; ?></strong><br>
                <strong class="float-right">Name : <?php echo $order->customer['f_name'].' '.$order->customer['l_name']; ?></strong><br>
                <strong class="float-right">Phone : <?php echo $order->customer['phone']; ?></strong><br>
                <strong class="float-right">Delivery Address : <?php if(isset($order->delivery_address)){ echo $order->delivery_address['address']; } else { echo ''; } ?></strong><br>
            <?php } ?>
        </td>
    </tr>
</table>
<table style="width: 100%;">
    <tr style="">
        <td>
            <b>Order details : </b> <span class="badge badge-soft-dark rounded-circle ml-1"><?php echo $order->details->count(); ?></span>
        </td>
    </tr>
    <tr style="">
        <td>
            <b>Order Note : </b><?php echo $order['order_note']; ?>
        </td>
    </tr>
    <tr style="">
        <td>
            <b>Payment Method : </b><?php str_replace('_',' ',$order['payment_method']); ?>
        </td>
    </tr>
    <tr style="">
        <td>
            <b>Order Type : </b><?php
            if($order['order_type'] == 1){
                echo "Home";
            } elseif ($order['order_type'] == 2){

            } elseif ($order['order_type'] == 3){

            } elseif($order['order_type'] == 4){

            }
            //echo str_replace('_',' ',$order['order_type']); 
            ?>
        </td>
    </tr>
</table>
<main id="content" role="main" class="main pointer-event">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="float-right">#INVOICE</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-4">
                <img width="150" src="<?php echo asset('storage/app/public/restaurant').'/'.$bisData['logo']; ?>">
                <br><br>
                <strong>Phone : <?php echo $bisData['phone']; ?></strong><br>
                <strong>Email : <?php echo $bisData['email_address']; ?></strong><br>
                <strong>Address : <?php echo $bisData['address']; ?></strong><br><br>
            </div>
            <div class="col-4"></div>
            <div class="col-4">
                <?php if($order->customer){ ?>
                    <strong class="float-right">Order ID : <?php echo $order['id']; ?></strong><br>
                    <strong class="float-right">Customer Name
                        : <?php echo $order->customer['f_name'].' '.$order->customer['l_name']; ?></strong><br>
                    <strong class="float-right">Phone
                        : <?php echo $order->customer['phone']; ?></strong><br>
                    <strong class="float-right">Delivery Address
                        : <?php if(isset($order->delivery_address)){ echo $order->delivery_address['address']; } else { echo ''; } ?></strong><br>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-3">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header" style="display: block!important;">
                        <div class="row">
                            <div class="col-12 pb-2 border-bottom">
                                <h4 class="card-header-title">
                                    Order details
                                    <span
                                        class="badge badge-soft-dark rounded-circle ml-1"><?php echo $order->details->count(); ?></span>
                                </h4>
                            </div>
                            <div class="col-6 pt-2">
                                <h6 style="color: #8a8a8a;">
                                    Order Note : <?php echo $order['order_note']; ?>
                                </h6>
                            </div>
                            <div class="col-6 pt-2">
                                <div class="text-right">
                                    <h6 class="text-capitalize" style="color: #8a8a8a;">
                                        Payment Method : <?php str_replace('_',' ',$order['payment_method']); ?>
                                    </h6>
                                    <h6 class="" style="color: #8a8a8a;">
                                        <?php if($order['transaction_reference']==null){ ?>
                                            Reference Code :
                                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                                    data-target=".bd-example-modal-sm">
                                                Add
                                            </button>
                                        <?php } else { ?>
                                            Reference Code : <?php echo $order['transaction_reference']; ?>
                                        <?php } ?>
                                    </h6>
                                    <h6 class="text-capitalize" style="color: #8a8a8a;">Order Type
                                        : <?php echo str_replace('_',' ',$order['order_type']); ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body">
                    <?php $sub_total=0;
                    $total_tax=0;
                    $total_dis_on_pro=0;
                    foreach($order->details as $detail){
                        if($detail->product){ ?>
                            <!-- Media -->
                                <div class="media">
                                    <div class="avatar avatar-xl mr-3">
                                        <img class="img-fluid"
                                             src="<?php echo asset('storage/app/public/product').'/'.json_decode($detail->product['image'],true)[0]; ?>"
                                             onerror="this.src='<?php echo asset('public/assets/admin/img/160x160/img2.jpg'); ?>'"
                                             alt="Image Description">
                                    </div>

                                    <div class="media-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3 mb-md-0">
                                                <strong> <?php echo $detail->product['name']; ?></strong><br>

                                                <?php if(count(json_decode($detail['variation'],true))>0){ ?>
                                                    <strong><u>Variation : </u></strong>
                                                    <?php foreach(json_decode($detail['variation'],true)[0] as $key1 =>$variation){ ?>
                                                        <div class="font-size-sm text-body">
                                                            <span><?php echo $key1; ?> :  </span>
                                                            <span class="font-weight-bold"><?php echo $variation; ?></span>
                                                        </div>
                                                    <?php }
                                                } ?>
                                            </div>

                                            <div class="col col-md-2 align-self-center">
                                                <?php if($detail['discount_on_product']!=0){ ?>
                                                    <h5>
                                                        <strike>
                                                            <?php echo HELPER::variation_price(json_decode($detail['product_details'],true),$detail['variation'])
                                                            ." ".HELPER::currency_symbol(); ?>
                                                        </strike>
                                                    </h5>
                                                <?php } ?>
                                                <h6><?php echo $detail['price']-$detail['discount_on_product'] ." ".HELPER::currency_symbol(); ?></h6>
                                            </div>
                                            <div class="col col-md-1 align-self-center">
                                                <h5><?php echo $detail['quantity']; ?></h5>
                                            </div>

                                            <div class="col col-md-2 align-self-center">
                                                <h5><?php echo $detail->product['capacity'].' '.$detail['unit'];?></h5>
                                            </div>

                                            <div class="col col-md-3 align-self-center text-right">
                                                <?php echo ($amount=($detail['price']-$detail['discount_on_product'])*$detail['quantity']); ?>
                                                <h5><?php echo $amount." ".HELPER::currency_symbol(); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php echo ($sub_total+=$amount); ?>
                                <?php echo ($total_tax+=$detail['tax_amount']*$detail['quantity']); ?>
                            <!-- End Media -->
                                <hr>
                            <?php }
                    } ?>

                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-sm-right">
                                    <dt class="col-sm-6">Items Price:</dt>
                                    <dd class="col-sm-6"><?php echo $sub_total." ".HELPER::currency_symbol(); ?></dd>
                                    <dt class="col-sm-6">Tax / VAT:</dt>
                                    <dd class="col-sm-6"><?php echo $total_tax." ".HELPER::currency_symbol(); ?></dd>

                                    <dt class="col-sm-6">Subtotal:</dt>
                                    <dd class="col-sm-6">
                                        <?php echo $sub_total+$total_tax." ".HELPER::currency_symbol(); ?></dd>
                                    <dt class="col-sm-6">Coupon Discount:</dt>
                                    <dd class="col-sm-6">
                                        - <?php echo $order['coupon_discount_amount']." ".HELPER::currency_symbol(); ?></dd>
                                    <dt class="col-sm-6">Delivery Fee:</dt>
                                    <dd class="col-sm-6">
                                        <?php if($order['order_type']=='self_pickup'){ 
                                            $del_c=0;
                                        } else {
                                            $del_c=$order['delivery_charge'];
                                        } ?>
                                        <?php echo $del_c." ".HELPER::currency_symbol(); ?>
                                        <hr>
                                    </dd>

                                    <dt class="col-sm-6">Total:</dt>
                                    <dd class="col-sm-6"><?php echo $sub_total+$del_c+$total_tax-$order['coupon_discount_amount']." ".HELPER::currency_symbol(); ?></dd>
                                </dl>
                                <!-- End Row -->
                            </div>
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="row justify-content-between align-items-center">
            <div class="col">
                <p class="font-size-sm mb-0">
                    &copy; <?php echo $bisData['restaurant_name']; ?>. <span
                        class="d-none d-sm-inline-block"><?php echo $bisData['footer_text']; ?></span>
                </p>
            </div>
        </div>
    </div>
</main>