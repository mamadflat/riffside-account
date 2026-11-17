<?php
// All customer and reward data is read on the server before first paint.
defined('ABSPATH') || exit;
use Riffside\Account\Plugin;
use Riffside\Account\Dashboard;
$d=Dashboard::data();$user=$d['user'];$club=$d['club'];$menu=wc_get_account_menu_items();
?>
<div class="ra-home" data-ra-home>
<header class="ra-home-welcome"><div><p>حساب من</p><h1>سلام <bdi><?php echo esc_html($user->first_name?:$user->display_name); ?></bdi></h1><span>سفارش‌ها و اعتبارت را از اینجا پیگیری کن.</span></div><span class="ra-welcome-icon"><?php echo Plugin::icon('account'); ?></span></header>
<div class="ra-home-overview">
<?php if(!empty($club['available'])): ?>
<section class="ra-home-card ra-wallet" aria-labelledby="ra-wallet-title" data-ra-widget="club">
 <div class="ra-wallet-heading"><span class="ra-home-icon"><?php echo Plugin::icon('customer-club'); ?></span><h2 id="ra-wallet-title">اعتبار باشگاه</h2><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('customer-club')); ?>">باشگاه من <?php echo Plugin::icon('arrow-left'); ?></a></div>
 <div class="ra-wallet-values"><div><span>اعتبار خرید</span><strong><bdi><?php echo esc_html(Dashboard::digits(number_format_i18n($club['credit_amount'],wc_get_price_decimals()))); ?></bdi> <small><?php echo esc_html(html_entity_decode($club['currency'],ENT_QUOTES,'UTF-8')); ?></small></strong></div><div class="ra-coin-balance"><strong><?php echo esc_html(Plugin::number($club['balance'])); ?></strong><span><?php echo esc_html($club['unit']); ?></span></div></div>
 <p>جزئیات موجودی و شرایط استفاده را در باشگاه ببین.</p>
</section>
<?php else: ?>
<section class="ra-home-card ra-wallet" data-ra-widget="account"><span class="ra-home-icon"><?php echo Plugin::icon('account'); ?></span><h2>اطلاعات حسابت</h2><p>مشخصات و نشانی‌هایت را بررسی کن تا برای خرید بعدی آماده باشی.</p><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('edit-account')); ?>">ویرایش حساب <?php echo Plugin::icon('arrow-left'); ?></a></section>
<?php endif; ?>
<div class="ra-order-totals" data-ra-widget="order-counts"><a class="ra-home-card" href="#ra-current-orders"><?php echo Plugin::icon('clock'); ?><strong><?php echo esc_html(Plugin::number($d['active_count'])); ?></strong><span>سفارش جاری</span></a><a class="ra-home-card" href="<?php echo esc_url(Plugin::url('orders')); ?>"><?php echo Plugin::icon('store'); ?><strong><?php echo esc_html(Plugin::number($d['order_count'])); ?></strong><span>همهٔ سفارش‌ها</span></a></div>
</div>
<nav class="ra-home-shortcuts" aria-label="کارهای پرکاربرد" data-ra-widget="shortcuts">
<?php $shortcuts=['orders'=>['store','سفارش‌های من'],'customer-club'=>['customer-club','باشگاه'],'edit-address'=>['corporate','نشانی‌ها'],'edit-account'=>['account','اطلاعات حساب']];foreach($shortcuts as $key=>$item):if(!isset($menu[$key]))continue; ?>
<a href="<?php echo esc_url(Plugin::url($key)); ?>"><span class="ra-shortcut-icon"><?php echo Plugin::icon($item[0]); ?></span><span><?php echo esc_html($item[1]); ?></span></a>
<?php endforeach; ?>
</nav>
<div class="ra-home-sections">
<section class="ra-home-card ra-current-orders" id="ra-current-orders" aria-labelledby="ra-orders-title" data-ra-widget="current-orders">
 <header class="ra-section-heading"><h2 id="ra-orders-title">سفارش‌های جاری</h2><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('orders')); ?>">همهٔ سفارش‌ها <?php echo Plugin::icon('arrow-left'); ?></a></header>
 <?php if(!$d['orders']): ?><div class="ra-home-empty"><span class="ra-home-icon"><?php echo Plugin::icon('store'); ?></span><h3>سفارش جاری نداری</h3><p>وضعیت سفارش بعدی‌ات اینجا نمایش داده می‌شود.</p><a class="ra-button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">دیدن محصولات</a></div><?php else: ?>
 <div class="ra-current-list"><?php foreach($d['orders'] as $order): ?>
 <article class="ra-current-order"><div class="ra-order-title"><strong>سفارش <bdi dir="ltr">#<?php echo esc_html($order->get_order_number()); ?></bdi></strong><span class="ra-status"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></div>
 <p class="ra-order-products"><?php $names=[];foreach($order->get_items() as $item){$names[]=$item->get_name();if(count($names)===2)break;}echo esc_html(implode('، ',$names)); ?></p><p class="ra-order-hint"><?php echo esc_html(Dashboard::order_hint($order)); ?></p>
 <div class="ra-order-action"><strong><?php echo wp_kses_post(Dashboard::digits($order->get_formatted_order_total())); ?></strong><a class="ra-button<?php echo $order->needs_payment()?' ra-primary':''; ?>" href="<?php echo esc_url($order->needs_payment()?$order->get_checkout_payment_url():$order->get_view_order_url()); ?>"><?php echo $order->needs_payment()?'پرداخت سفارش':'پیگیری سفارش'; ?></a></div>
 </article><?php endforeach; ?></div><?php endif; ?>
</section>
<?php if(!empty($club['available'])): ?>
<section class="ra-home-card ra-earn" aria-labelledby="ra-earn-title" data-ra-widget="rewards"><header class="ra-section-heading"><h2 id="ra-earn-title">راه‌های دریافت <?php echo esc_html($club['unit']); ?></h2></header>
<?php if(!empty($club['review'])): ?><article class="ra-earn-item"><span class="ra-home-icon"><?php echo Plugin::icon('files'); ?></span><div><h3>نظرت را دربارهٔ محصول بنویس</h3><span class="ra-reward-label"><?php echo esc_html(Plugin::number($club['review']['points']).' '.$club['unit']); ?> پاداش</span><p><?php echo !empty($club['review']['verified_only'])?'پس از تأیید نظر برای محصولی که خریده‌ای، پاداش می‌گیری.':'پس از تأیید نظر واجد شرایط، پاداش می‌گیری.'; ?><?php if($club['review']['daily_limit']>0)echo esc_html(' پاداش روزانه برای حداکثر '.Plugin::number($club['review']['daily_limit']).' نظر است.'); ?></p><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('orders')); ?>">انتخاب سفارش <?php echo Plugin::icon('arrow-left'); ?></a></div></article><?php endif; ?>
<?php if(!empty($club['referral'])): ?><article class="ra-earn-item"><span class="ra-home-icon"><?php echo Plugin::icon('customer-club'); ?></span><div><h3>دوستانت را دعوت کن</h3><span class="ra-reward-label"><?php echo esc_html(Plugin::number($club['referral']['points']).' '.$club['unit']); ?> پاداش</span><p>به‌ازای هر <?php echo esc_html(Dashboard::digits(number_format_i18n($club['referral']['spend_amount'],wc_get_price_decimals())).' '.html_entity_decode($club['currency'],ENT_QUOTES,'UTF-8')); ?> خرید واجد شرایط دوستانی که دعوت کرده‌ای، پاداش می‌گیری.</p><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('customer-club-referral')); ?>">دریافت لینک دعوت <?php echo Plugin::icon('arrow-left'); ?></a></div></article><?php endif; ?>
<?php if(empty($club['review'])&&empty($club['referral'])): ?><div class="ra-home-empty"><p>روش‌ها و شرایط دریافت <?php echo esc_html($club['unit']); ?> را در باشگاه ببین.</p><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('customer-club')); ?>">مشاهدهٔ باشگاه</a></div><?php endif; ?>
</section>
<?php if(is_array($club['activities']??null)): ?><section class="ra-home-card ra-activity" aria-labelledby="ra-activity-title" data-ra-widget="activity"><header class="ra-section-heading"><h2 id="ra-activity-title">فعالیت‌های باشگاه</h2><a class="ra-text-link" href="<?php echo esc_url(Plugin::url('customer-club-transactions')); ?>">همهٔ فعالیت‌ها <?php echo Plugin::icon('arrow-left'); ?></a></header>
<?php if(!empty($club['activities'])): ?><ul><?php foreach($club['activities'] as $activity): ?><li><span class="ra-home-icon"><?php echo Plugin::icon('clock'); ?></span><div><strong><?php echo esc_html($activity['label']); ?></strong><small><?php echo esc_html(Dashboard::digits($activity['date'])); ?></small></div><bdi dir="ltr"><?php echo esc_html(($activity['amount']>0?'+':'−').Plugin::number(abs($activity['amount']))); ?></bdi></li><?php endforeach; ?></ul><?php else: ?><div class="ra-activity-empty"><?php echo Plugin::icon('clock'); ?><p>هنوز فعالیتی ثبت نشده. پس از دریافت یا مصرف <?php echo esc_html($club['unit']); ?>، سابقه‌اش اینجا نمایش داده می‌شود.</p></div><?php endif; ?>
</section><?php endif; ?>
<?php endif; ?>
</div>
</div>
<?php do_action('woocommerce_account_dashboard'); do_action('woocommerce_before_my_account'); do_action('woocommerce_after_my_account'); ?>