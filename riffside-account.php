<?php
/**
 * Plugin Name: پنل کاربری ریف‌ساید
 * Description: حساب یکپارچهٔ ووکامرس، باشگاه مشتریان و پشتیبانی با ظاهر قابل تنظیم و فونت میزبان.
 * Version: 0.3.0
 * Author: Riffside
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Text Domain: riffside-account
 * License: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;
define('RIFFSIDE_ACCOUNT_FILE', __FILE__);
define('RIFFSIDE_ACCOUNT_DIR', __DIR__ . '/');
define('RIFFSIDE_ACCOUNT_VERSION', '0.3.0');
require_once __DIR__ . '/src/Appearance.php';
require_once __DIR__ . '/src/Support.php';
require_once __DIR__ . '/src/Plugin.php';
register_activation_hook(__FILE__, ['Riffside\Account\Plugin','activate']);
register_deactivation_hook(__FILE__, static function () { flush_rewrite_rules(false); });
add_action('plugins_loaded', ['Riffside\Account\Plugin','boot'], 30);
add_action('before_woocommerce_init', static function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
});


