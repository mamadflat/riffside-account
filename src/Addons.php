<?php
namespace Riffside\Account;

defined('ABSPATH') || exit;

final class Addons {
    private const CLUB_PLUGIN = 'customer-club/customer-club.php';
    private const CLUB_PRODUCT = 'https://riffside.ir/plugins/customer-club/';

    public static function register_menu() {
        add_submenu_page(
            'riffside-account',
            'افزونه‌های پنل ریف‌ساید',
            'افزونه‌ها',
            'manage_woocommerce',
            'riffside-account-addons',
            [self::class, 'render']
        );
    }

    public static function render() {
        if (!current_user_can('manage_woocommerce')) return;

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $active = is_plugin_active(self::CLUB_PLUGIN);
        $installed = $active || isset(get_plugins()[self::CLUB_PLUGIN]);
        $activation_url = '';
        if ($installed && !$active && current_user_can('activate_plugin', self::CLUB_PLUGIN)) {
            $activation_url = wp_nonce_url(
                add_query_arg(['action' => 'activate', 'plugin' => self::CLUB_PLUGIN], admin_url('plugins.php')),
                'activate-plugin_' . self::CLUB_PLUGIN
            );
        }

        $product_url = self::CLUB_PRODUCT;
        require RIFFSIDE_ACCOUNT_DIR . 'templates/admin-addons.php';
    }
}
