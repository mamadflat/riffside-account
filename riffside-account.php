<?php
/**
 * Plugin Name: پنل کاربری ریف‌ساید
 * Description: حساب مشتری ووکامرس با تم‌های مستقل و امکانات اختیاری.
 * Version: 1.0.5
 * Author: Riffside
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Text Domain: riffside-account
 * License: GPL-2.0-or-later
 */

use Riffside\Account\Plugin;

defined('ABSPATH') || exit;

define('RIFFSIDE_ACCOUNT_FILE', __FILE__);
define('RIFFSIDE_ACCOUNT_DIR', __DIR__ . '/');
define('RIFFSIDE_ACCOUNT_VERSION', '1.0.5');

foreach (['Appearance', 'Dashboard', 'Extensions', 'Orders', 'Support', 'PortalTickets', 'Modules', 'Addons', 'Receipt', 'Plugin'] as $component) {
    require_once RIFFSIDE_ACCOUNT_DIR . 'src/' . $component . '.php';
}

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
add_action('plugins_loaded', [Plugin::class, 'boot'], 30);
add_action('before_woocommerce_init', static function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
