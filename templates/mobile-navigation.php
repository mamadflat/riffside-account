<?php
defined('ABSPATH') || exit;
use Riffside\Account\Plugin;
$destinations=['dashboard'=>['services','خانه'],'orders'=>['store','سفارش‌ها']];
if(isset(wc_get_account_menu_items()['customer-club']))$destinations['customer-club']=['customer-club','باشگاه'];
$destinations['edit-account']=['account','حساب من'];
?>
<nav class="ra-mobile-nav" aria-label="دسترسی سریع حساب">
<?php foreach($destinations as $endpoint=>$item): ?>
<a href="<?php echo esc_url(Plugin::url($endpoint)); ?>" <?php if(wc_is_current_account_menu_item($endpoint)||($endpoint==='customer-club'&&(is_wc_endpoint_url('customer-club-transactions')||is_wc_endpoint_url('customer-club-referral'))))echo 'aria-current="page"'; ?>><?php echo Plugin::icon($item[0]); ?><span><?php echo esc_html($item[1]); ?></span></a>
<?php endforeach; ?>
</nav>
