<?php
/** WooCommerce 5.2.0 line item hooks, with localized display numbers. */
defined('ABSPATH') || exit;
use Riffside\Account\Orders;
use Riffside\Account\Dashboard;
if(!Orders::owns($order)||!apply_filters('woocommerce_order_item_visible',true,$item))return;
$visible=$product&&$product->is_visible();
$permalink=apply_filters('woocommerce_order_item_permalink',$visible?$product->get_permalink($item):'',$item,$order);
$qty=$item->get_quantity();$refunded_qty=$order->get_qty_refunded_for_item($item_id);
$qty_display=$refunded_qty?'<del>'.esc_html(Dashboard::digits($qty)).'</del> <ins>'.esc_html(Dashboard::digits($qty+$refunded_qty)).'</ins>':esc_html(Dashboard::digits($qty));
?>
<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class','woocommerce-table__line-item order_item',$item,$order)); ?>">
<td class="woocommerce-table__product-name product-name">
<?php echo wp_kses_post(apply_filters('woocommerce_order_item_name',$permalink?'<a href="'.esc_url($permalink).'">'.esc_html($item->get_name()).'</a>':esc_html($item->get_name()),$item,$visible)); ?>
<?php echo wp_kses_post(apply_filters('woocommerce_order_item_quantity_html',' <strong class="product-quantity"> × '.$qty_display.'</strong>',$item)); ?>
<?php do_action('woocommerce_order_item_meta_start',$item_id,$item,$order,false);wc_display_item_meta($item);do_action('woocommerce_order_item_meta_end',$item_id,$item,$order,false); ?>
</td><td class="woocommerce-table__product-total product-total"><span class="ra-line-total-label">مبلغ: </span><?php echo wp_kses_post(Dashboard::digits($order->get_formatted_line_subtotal($item))); ?></td>
</tr>
<?php if($show_purchase_note&&$purchase_note): ?><tr class="woocommerce-table__product-purchase-note product-purchase-note"><td colspan="2"><?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?></td></tr><?php endif; ?>
