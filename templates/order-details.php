<?php
/** Account-only layout, based on the WooCommerce 10.9.0 order details hook contract. */
defined('ABSPATH') || exit;
use Riffside\Account\Orders;
use Riffside\Account\Dashboard;
$order=wc_get_order($order_id);if(!Orders::owns($order))return;
$order_items=$order->get_items(apply_filters('woocommerce_purchase_order_item_types','line_item'));
$show_purchase_note=$order->has_status(apply_filters('woocommerce_purchase_note_order_statuses',['completed','processing']));
if(!empty($show_downloads))wc_get_template('order/order-downloads.php',['downloads'=>$order->get_downloadable_items(),'show_title'=>true]);
?>
<section class="woocommerce-order-details ra-detail-layout">
<?php do_action('woocommerce_order_details_before_order_table',$order); ?>
<section class="ra-card ra-detail-products"><h2>کالاهای سفارش</h2>
<table class="woocommerce-table woocommerce-table--order-details shop_table order_details"><thead><tr><th scope="col">کالا و تعداد</th><th scope="col">مبلغ</th></tr></thead><tbody>
<?php do_action('woocommerce_order_details_before_order_table_items',$order); ?>
<?php foreach($order_items as $item_id=>$item):$product=$item->get_product();wc_get_template('order/order-details-item.php',['order'=>$order,'item_id'=>$item_id,'item'=>$item,'show_purchase_note'=>$show_purchase_note,'purchase_note'=>$product?$product->get_purchase_note():'','product'=>$product]);endforeach; ?>
<?php do_action('woocommerce_order_details_after_order_table_items',$order); ?>
</tbody></table>
<?php if($order->get_customer_note()): ?><div class="ra-customer-note"><h3>یادداشت تو برای این سفارش</h3><p><?php echo wp_kses(nl2br(wc_wptexturize_order_note($order->get_customer_note())),['br'=>[]]); ?></p></div><?php endif; ?>
</section>
<section class="ra-card ra-detail-payment"><h2>خلاصهٔ پرداخت</h2><dl>
<?php foreach($order->get_order_item_totals() as $key=>$total): ?><div class="<?php echo $key==='order_total'?'ra-total-row':''; ?>"><dt><?php echo esc_html($total['label']); ?></dt><dd><?php echo wp_kses_post(Dashboard::digits($total['value'])); ?></dd></div><?php endforeach; ?>
</dl><div class="ra-order-actions"><?php Orders::actions($order,true); ?></div></section>
<div class="ra-order-extensions"><?php do_action('woocommerce_order_details_after_order_table',$order); ?></div>
</section>
<?php do_action('woocommerce_after_order_details',$order); ?>
<?php wc_get_template('order/order-details-customer.php',['order'=>$order]); ?>
