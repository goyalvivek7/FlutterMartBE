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
            <b>Order Date : </b> <?php echo $order->created_at; ?>
        </td>
    </tr>
    <tr style="">
        <td>
            <b>Order details : </b> <?php echo $order->details->count(); ?>
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
                echo "Home Delivery";
            } elseif ($order['order_type'] == 2){
                echo "Store Pickup";
            } elseif ($order['order_type'] == 3){
                echo "Prime Delivery";
            } elseif($order['order_type'] == 4){
                echo "Scan & Pay";
            }
            //echo str_replace('_',' ',$order['order_type']); 
            ?>
        </td>
    </tr>
</table>
<hr />
<table width="100%">
    <tr>
        <th>QTY</th>
        <th>PRODUCT IMAGE</th>
        <th>DESC</th>
        <th>PRICE</th>
      	<th>QUANTITY</th>
      	<th>TOTAL</th>
    </tr>
    <?php $sub_total=0; $total_tax=0; $total_dis_on_pro=0; $amount = 0;
    foreach($order->details as $detail){
        if($detail->product){ ?>
            <tr>
                <td>
                    <?php echo $detail->quantity; ?>
                </td>
                <td>
                    <img width="80px" class="img-fluid" src="<?php echo asset('storage/app/public/product').'/'.json_decode($detail->product['image'],true)[0]; ?>" onerror="this.src='<?php echo asset('public/assets/admin/img/160x160/img2.jpg'); ?>'"alt="Image Description">
                </td>
                <td>
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
                </td>
                <td>
                    <?php //echo $detail['price']-$detail['discount_on_product'] ." "; ?>
                  	<s><?php echo $detail->product_org_price." Rs"; ?></s> - <?php echo $detail->price ." Rs"."  ( -".$detail->discount_on_product." Rs"." Extra Discount)"; ?>
                </td>
              <td><?php echo $detail->quantity; ?></td>
              <td>
              	<?php $amount=(($detail->price - $detail->discount_on_product)*$detail->quantity); ?>
                <?php $amount." Rs"; ?>
                <?php echo $amount; ?>
              </td>
            </tr>
        <?php }
    } ?>
</table>
<table width="100%">
    <tr><td>Item Price: <?php echo $cartData->basic_amount; ?></td></tr>
    <tr><td>Item Discount: <?php echo $cartData->basic_amount - $cartData->total_amount; ?></td></tr>
    <tr><td>Item Total: <?php echo $cartData->total_amount; ?></td></tr>
    <tr><td>Tax: <?php echo $cartData->tax_amount; ?></td></tr>
    <tr><td>Delivery Charge: <?php echo $cartData->delivery_charge; ?></td></tr>
    <tr><td>Coupon Discount: - <?php echo $cartData->coupon_discount; ?></td></tr>
    <tr><td>Wallet Deduction: - <?php echo $cartData->wallet_balance; ?></td></tr>
    <tr><td>Sub Total: <?php echo $cartData->remaining_sub_total; ?></td></tr>
    <hr />
    <tr><td><strong>Total: <?php echo $cartData->final_amount; ?></strong></td></tr>
</table>