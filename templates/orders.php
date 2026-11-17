<?php
/** Account order cards. Keeps the WooCommerce 9.5.0 orders template extension hooks. */
defined('ABSPATH') || exit;
use Riffside\Account\Orders;
use Riffside\Account\Plugin;
use Riffside\Account\Dashboard;
$group=Orders::group();$groups=Orders::groups();$counts=Orders::counts();$current_page=Orders::page($current_page);
?>
<div class="ra-orders" data-ra-orders>
<header class="ra-page-head"><h1>سفارش‌های من</h1><p>وضعیت سفارش‌ها را ببین و جزئیات هرکدام را پیگیری کن.</p></header>
<nav class="ra-order-tabs" aria-label="دسته‌بندی سفارش‌ها">
<?php foreach($groups as $key=>$tab): ?><a href="<?php echo esc_url(Orders::url($key)); ?>" <?php if($key===$group)echo 'aria-current="page"'; ?>><span><?php echo esc_html($tab['label']); ?></span><strong><?php echo esc_html(Plugin::number($counts[$key])); ?></strong></a><?php endforeach; ?>
</nav>
<p class="ra-orders-description"><?php echo esc_html($groups[$group]['hint']); ?></p>
<?php do_action('woocommerce_before_account_orders',$has_orders); ?>
<?php if($customer_orders->orders): ?>
<div class="ra-orders-list">
<?php foreach($customer_orders->orders as $customer_order): $order=wc_get_order($customer_order);if(!Orders::owns($order))continue; ?>
<article class="ra-order-card" data-order-id="<?php echo esc_attr($order->get_id()); ?>">
 <header class="ra-order-card-head"><a class="ra-order-number" href="<?php echo esc_url(Orders::view_url($order,$current_page)); ?>">سفارش <bdi dir="ltr">#<?php echo esc_html($order->get_order_number()); ?></bdi></a><span class="ra-order-status"><?php if(has_action('woocommerce_my_account_my_orders_column_order-status'))do_action('woocommerce_my_account_my_orders_column_order-status',$order);else echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></header>
 <div class="ra-order-preview"><span class="ra-order-picture" aria-hidden="true"><?php echo Plugin::icon('store'); ?></span><div><p><?php $names=[];foreach($order->get_items() as $item){if(!apply_filters('woocommerce_order_item_visible',true,$item))continue;$names[]=$item->get_name();if(count($names)===2)break;}echo esc_html(implode('، ',$names)?:'جزئیات کالاها را در سفارش ببین.'); ?></p><small><?php echo esc_html(Plugin::number($order->get_item_count()).' کالا'); ?> · <?php echo esc_html(Orders::date($order->get_date_created())); ?></small></div></div>
 <p class="ra-order-status-hint"><?php echo esc_html(Orders::hint($order)); ?></p>
 <?php foreach(wc_get_account_orders_columns() as $column=>$label): if(in_array($column,['order-number','order-date','order-status','order-total','order-actions'],true)||!has_action('woocommerce_my_account_my_orders_column_'.$column))continue; ?><div class="ra-order-extra"><span><?php echo esc_html($label); ?></span><?php do_action('woocommerce_my_account_my_orders_column_'.$column,$order); ?></div><?php endforeach; ?>
 <footer class="ra-order-card-foot"><div class="ra-order-amount"><small>مبلغ سفارش</small><strong><?php echo wp_kses_post(Dashboard::digits($order->get_formatted_order_total())); ?></strong></div><div class="ra-order-actions"><?php if(has_action('woocommerce_my_account_my_orders_column_order-actions'))do_action('woocommerce_my_account_my_orders_column_order-actions',$order);else Orders::actions($order,false,$current_page); ?></div></footer>
</article>
<?php endforeach; ?>
</div>
<?php do_action('woocommerce_before_account_orders_pagination'); ?>
<?php if($customer_orders->max_num_pages>1): ?><nav class="ra-order-pagination" aria-label="صفحه‌های سفارش‌ها"><?php if($current_page>1): ?><a class="ra-button" rel="prev" href="<?php echo esc_url(Orders::url($group,$current_page-1)); ?>">صفحهٔ قبل</a><?php endif; ?><span>صفحهٔ <?php echo esc_html(Plugin::number($current_page).' از '.Plugin::number($customer_orders->max_num_pages)); ?></span><?php if($current_page<$customer_orders->max_num_pages): ?><a class="ra-button" rel="next" href="<?php echo esc_url(Orders::url($group,$current_page+1)); ?>">صفحهٔ بعد</a><?php endif; ?></nav><?php endif; ?>
<?php else: ?>
<section class="ra-order-empty"><?php echo Plugin::icon('store'); ?><h2><?php echo esc_html($current_page>1?'در این صفحه سفارشی نیست':$groups[$group]['empty']); ?></h2><p><?php echo esc_html($groups[$group]['hint']); ?></p><a class="ra-button" href="<?php echo esc_url($current_page>1?Orders::url($group):wc_get_page_permalink('shop')); ?>"><?php echo $current_page>1?'برگشت به صفحهٔ اول':'دیدن محصولات'; ?></a></section>
<?php endif; ?>
<?php do_action('woocommerce_after_account_orders',$has_orders); ?>
</div>
