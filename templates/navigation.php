<?php
defined('ABSPATH') || exit;
use Riffside\Account\Plugin;
use Riffside\Account\Navigation;
do_action('woocommerce_before_account_navigation');
?>
<nav class="woocommerce-MyAccount-navigation ra-navigation" aria-label="بخش‌های حساب کاربری">
<?php foreach(wc_get_account_menu_items() as $endpoint=>$label): ?>
<a href="<?php echo esc_url(Plugin::url($endpoint)); ?>" <?php if(wc_is_current_account_menu_item($endpoint))echo 'aria-current="page"'; ?>><?php echo Plugin::icon(Navigation::icon($endpoint)); ?><span><?php echo esc_html(wp_strip_all_tags($label)); ?></span></a>
<?php endforeach; ?>
</nav>
<?php do_action('woocommerce_after_account_navigation'); ?>
