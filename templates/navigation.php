<?php
defined('ABSPATH') || exit;
use Riffside\Account\Plugin;
$icons=['dashboard'=>'services','orders'=>'store','downloads'=>'download','customer-club'=>'customer-club','customer-club-transactions'=>'customer-club','edit-account'=>'account','edit-address'=>'corporate','licenses'=>'plugins','ra-support'=>'ticket','tickets'=>'ticket','customer-logout'=>'logout'];
do_action('woocommerce_before_account_navigation');
?>
<nav class="woocommerce-MyAccount-navigation ra-navigation" aria-label="بخش‌های حساب کاربری">
<?php foreach(wc_get_account_menu_items() as $endpoint=>$label): ?>
<a href="<?php echo esc_url(Plugin::url($endpoint)); ?>" <?php if(wc_is_current_account_menu_item($endpoint))echo 'aria-current="page"'; ?>><?php echo Plugin::icon(\Riffside\Account\Extensions::icon($endpoint,$icons[$endpoint]??'files')); ?><span><?php echo esc_html(wp_strip_all_tags($label)); ?></span></a>
<?php endforeach; ?>
</nav>
<?php do_action('woocommerce_after_account_navigation'); ?>
