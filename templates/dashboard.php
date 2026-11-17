<?php
defined('ABSPATH') || exit;
use Riffside\Account\Plugin;
use Riffside\Account\Support;
use Riffside\Account\Appearance;
$user=wp_get_current_user();
$orders=wc_get_orders(['customer_id'=>$user->ID,'limit'=>3,'orderby'=>'date','order'=>'DESC']);
$processing=wc_get_orders(['customer_id'=>$user->ID,'status'=>['wc-processing','wc-on-hold'],'limit'=>1,'paginate'=>true,'return'=>'ids']);
$total=wc_get_customer_order_count($user->ID);
$has_club=class_exists('\Customer_Club\Points_Ledger');
$points=$has_club?\Customer_Club\Points_Ledger::balance($user->ID):null;
?>
<div class="ra-page-head"><h1>سلام <?php echo esc_html($user->first_name?:$user->display_name); ?>، خوش آمدی.</h1><p>سفارش‌ها و اطلاعات حسابت، یک‌جا.</p></div>
<div class="ra-stats">
    <a class="ra-stat" href="<?php echo esc_url(Plugin::url('orders')); ?>"><?php echo Plugin::icon('store'); ?><span>سفارش در جریان</span><strong><?php echo esc_html(Plugin::number($processing->total)); ?></strong><small>از <?php echo esc_html(Plugin::number($total)); ?> سفارش</small></a>
    <?php if($has_club): ?><a class="ra-stat ra-stat-accent" href="<?php echo esc_url(Plugin::url('customer-club')); ?>"><?php echo Plugin::icon('customer-club'); ?><span>موجودی باشگاه</span><strong><?php echo esc_html(Plugin::number($points)); ?></strong><small><?php echo esc_html(\Customer_Club\Coin::name()); ?></small></a><?php elseif(isset(wc_get_account_menu_items()['downloads'])): ?><a class="ra-stat" href="<?php echo esc_url(Plugin::url('downloads')); ?>"><?php echo Plugin::icon('download'); ?><span>دانلودها</span><strong><?php echo esc_html(Plugin::number(count(wc_get_customer_available_downloads($user->ID)))); ?></strong><small>فایل قابل دریافت</small></a><?php else: ?><a class="ra-stat" href="<?php echo esc_url(Plugin::url('edit-account')); ?>"><?php echo Plugin::icon('account'); ?><span>اطلاعات حساب</span><strong>پروفایل</strong><small>مشاهده و ویرایش</small></a><?php endif; ?>
    <?php if(Appearance::enabled('support')): ?><a class="ra-stat" href="<?php echo esc_url(Plugin::url(Plugin::support_endpoint())); ?>"><?php echo Plugin::icon('ticket'); ?><span>پشتیبانی</span><strong><?php echo class_exists('Riffside_Portal')?'گفت‌وگو':esc_html(Plugin::number(Support::count($user->ID))); ?></strong><small>تیکت‌ها و پاسخ‌ها</small></a><?php else: ?><a class="ra-stat" href="<?php echo esc_url(Plugin::url('orders')); ?>"><?php echo Plugin::icon('files'); ?><span>کل سفارش‌ها</span><strong><?php echo esc_html(Plugin::number($total)); ?></strong><small>سابقهٔ خریدهای شما</small></a><?php endif; ?>
</div>
<div class="ra-columns"><section class="ra-card"><div class="ra-card-head"><h2>آخرین سفارش‌ها</h2><a href="<?php echo esc_url(Plugin::url('orders')); ?>">همهٔ سفارش‌ها</a></div>
<?php if(!$orders): ?><div class="ra-empty"><?php echo Plugin::icon('store'); ?><h3>هنوز سفارشی ثبت نکرده‌ای.</h3><p>بعد از خرید، وضعیت سفارشت را همین‌جا دنبال کن.</p><a class="ra-button ra-primary" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">دیدن محصولات</a></div><?php else: foreach($orders as $order): ?>
<article class="ra-order"><div class="ra-row"><strong>سفارش <bdi dir="ltr">#<?php echo esc_html($order->get_order_number()); ?></bdi></strong><span class="ra-badge"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></div><p><?php $names=[];foreach($order->get_items() as $item){$names[]=$item->get_name();if(count($names)>=2)break;}echo esc_html(implode('، ',$names)); ?></p><div class="ra-row"><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong><a class="ra-button" href="<?php echo esc_url($order->get_view_order_url()); ?>">مشاهدهٔ سفارش</a></div></article>
<?php endforeach; endif; ?></section><div class="ra-stack"><?php if(Appearance::enabled('support')): ?><section class="ra-card"><h2>کنارت هستیم.</h2><p>برای پیگیری خرید یا پرسش دربارهٔ محصول، با پشتیبانی گفت‌وگو کن.</p><a class="ra-button ra-primary" href="<?php echo esc_url(Plugin::url(Plugin::support_endpoint())); ?>">گفت‌وگو با پشتیبانی</a></section><?php endif; ?><section class="ra-card"><h2>دسترسی سریع</h2><div class="ra-quick"><?php foreach([Appearance::settings()['license_endpoint']=>['plugins','لایسنس‌ها'],'downloads'=>['download','دانلودها'],'edit-address'=>['corporate','نشانی‌ها'],'edit-account'=>['account','اطلاعات من']] as $key=>$item): if(!isset(wc_get_account_menu_items()[$key]))continue; ?><a href="<?php echo esc_url(Plugin::url($key)); ?>"><?php echo Plugin::icon($item[0]); ?><span><?php echo esc_html($item[1]); ?></span></a><?php endforeach; ?></div></section></div></div>
<?php do_action('woocommerce_account_dashboard'); do_action('woocommerce_before_my_account'); do_action('woocommerce_after_my_account'); ?>


