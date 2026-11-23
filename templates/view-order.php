<?php
/** Private order view, preserving WooCommerce 10.6.0's customer notes and view hook. */
defined('ABSPATH') || exit;
use Riffside\Account\Orders;
use Riffside\Account\Plugin;
if(!Orders::owns($order))return;
$notes=$order->get_customer_order_notes();
?>
<div class="ra-order-view" data-ra-order-view>
<a class="ra-orders-back" href="<?php echo esc_url(Orders::back_url($order)); ?>"><?php echo Plugin::icon('arrow-left'); ?> برگشت به سفارش‌ها</a>
<header class="ra-section-heading ra-detail-heading"><span class="ra-section-icon"><?php echo Plugin::icon('store'); ?></span><div><h1>سفارش <bdi dir="ltr">#<?php echo esc_html($order->get_order_number()); ?></bdi></h1><p>ثبت‌شده در <?php echo esc_html(Orders::date($order->get_date_created())); ?></p></div><span class="ra-order-status" data-ra-order-tone="<?php echo esc_attr(Orders::tone($order->get_status())); ?>"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></header>
<section class="ra-order-state"><span class="ra-order-state-icon"><?php echo Plugin::icon($order->has_status('completed')?'check':'clock'); ?></span><div><h2>وضعیت سفارش</h2><p><?php echo wp_kses_post(apply_filters('woocommerce_order_details_status',Orders::hint($order),$order)); ?></p></div></section>
<?php do_action('woocommerce_view_order',$order_id); ?>
<?php if($notes): ?><section class="ra-card ra-order-notes"><h2>پیام‌های فروشگاه</h2><ol><?php foreach($notes as $note): ?><li><small><?php echo esc_html(Orders::date(new WC_DateTime($note->comment_date,wp_timezone()))); ?></small><div><?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?></div></li><?php endforeach; ?></ol></section><?php endif; ?>
</div>
