<?php
/** WooCommerce 8.7.0 address hooks, with bidi-safe customer contact data. */
defined('ABSPATH') || exit;
use Riffside\Account\Orders;
if(!Orders::owns($order))return;
$types=['billing'=>'نشانی صورتحساب'];
if(!wc_ship_to_billing_address_only()&&$order->needs_shipping_address())$types['shipping']='نشانی تحویل';
?>
<section class="woocommerce-customer-details ra-order-addresses">
<?php foreach($types as $type=>$label):$address=$type==='billing'?$order->get_formatted_billing_address('نشانی ثبت نشده'):$order->get_formatted_shipping_address('نشانی ثبت نشده');$phone=$type==='billing'?$order->get_billing_phone():$order->get_shipping_phone(); ?>
<section class="ra-card"><h2><?php echo esc_html($label); ?></h2><address><?php echo wp_kses_post($address); ?>
<?php if($phone): ?><p><span>تلفن: </span><bdi dir="ltr"><?php echo esc_html($phone); ?></bdi></p><?php endif; ?>
<?php if($type==='billing'&&$order->get_billing_email()): ?><p><span>ایمیل: </span><bdi dir="ltr"><?php echo esc_html($order->get_billing_email()); ?></bdi></p><?php endif; ?>
<?php do_action('woocommerce_order_details_after_customer_address',$type,$order); ?>
</address></section>
<?php endforeach; ?>
<?php do_action('woocommerce_order_details_after_customer_details',$order); ?>
</section>
