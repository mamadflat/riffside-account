<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class Navigation {
    const OPTION = 'riffside_account_navigation';
    const GROUP = 'riffside_account_navigation';
    const REQUIRED = ['dashboard', 'customer-logout'];

    public static function boot() {
        add_filter('woocommerce_account_menu_items', [self::class, 'menu'], PHP_INT_MAX);
        add_action('template_redirect', [self::class, 'guard'], 1);
        add_action('admin_menu', [self::class, 'register_menu'], 30);
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
    }

    public static function settings() {
        $settings = (array) get_option(self::OPTION, []);
        return [
            'order' => (array) ($settings['order'] ?? []),
            'hidden' => (array) ($settings['hidden'] ?? []),
        ];
    }

    public static function sanitize($input) {
        $input = is_array($input) ? $input : [];
        $previous = self::settings();
        $available = wc_get_account_menu_items();
        $order = array_intersect_key($available, array_flip(array_filter((array) ($input['order'] ?? []), 'is_string')));

        // Keep saved preferences for integrations that are temporarily unavailable.
        $shown = array_values(array_filter((array) ($input['order'] ?? []), static fn($key) => is_string($key) && isset($order[$key])));
        $hidden = array_diff($previous['hidden'], array_keys($order));
        $enabled = (array) ($input['enabled'] ?? []);
        foreach ($shown as $key) {
            if (!in_array($key, self::REQUIRED, true) && ($enabled[$key] ?? '') !== '1') {
                $hidden[] = $key;
            }
        }
        return [
            'order' => array_values(array_unique(array_merge($shown, $previous['order']))),
            'hidden' => array_values(array_unique(array_diff($hidden, self::REQUIRED))),
        ];
    }

    public static function ordered(array $menu, array $order) {
        $first = [];
        foreach ($order as $key) {
            if (isset($menu[$key]) && $key !== 'customer-logout') $first[$key] = $menu[$key];
        }
        $items = $first + $menu;
        if (isset($items['customer-logout'])) {
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);
            $items['customer-logout'] = $logout;
        }
        return $items;
    }

    public static function menu($items) {
        if (is_admin()) return $items;
        $settings = self::settings();
        return self::ordered(array_diff_key($items, array_flip($settings['hidden'])), $settings['order']);
    }

    public static function guard() {
        if (!is_account_page() || !is_user_logged_in()) return;
        global $wp;
        foreach (array_diff(self::settings()['hidden'], self::REQUIRED) as $key) {
            if (array_key_exists($key, $wp->query_vars)) {
                nocache_headers();
                wp_die('این بخش در حساب کاربری نمایش داده نمی‌شود.', 'بخش غیرفعال', ['response' => 404, 'back_link' => true]);
            }
        }
    }

    public static function icon($endpoint) {
        $icons = [
            'dashboard' => 'services', 'orders' => 'store', 'downloads' => 'download',
            'customer-club' => 'customer-club', 'customer-club-transactions' => 'customer-club',
            'edit-account' => 'account', 'edit-address' => 'corporate', 'licenses' => 'plugins',
            'ra-support' => 'ticket', 'tickets' => 'ticket', 'customer-logout' => 'logout',
        ];
        return Extensions::icon($endpoint, $icons[$endpoint] ?? 'files');
    }

    public static function register_menu() {
        add_submenu_page('riffside-account', 'منو و داشبورد', 'منو و داشبورد', 'manage_woocommerce',
            'riffside-account-navigation', [self::class, 'render']);
    }

    public static function register_settings() {
        register_setting(self::GROUP, self::OPTION, ['type' => 'array', 'sanitize_callback' => [self::class, 'sanitize']]);
        add_filter('option_page_capability_' . self::GROUP, static fn() => 'manage_woocommerce');
    }

    public static function assets($hook) {
        if (!str_ends_with($hook, '_page_riffside-account-navigation')) return;
        wp_enqueue_style('riffside-account-preview', plugins_url('assets/home.css', RIFFSIDE_ACCOUNT_FILE), ['riffside-account-admin'], RIFFSIDE_ACCOUNT_VERSION);
        wp_add_inline_style('riffside-account-preview', Appearance::css());
        wp_enqueue_script('riffside-account-navigation', plugins_url('assets/admin-navigation.js', RIFFSIDE_ACCOUNT_FILE), [], RIFFSIDE_ACCOUNT_VERSION, true);
    }

    public static function render() {
        if (!current_user_can('manage_woocommerce')) return;
        $settings = self::settings();
        $menu = self::ordered(wc_get_account_menu_items(), $settings['order']);
        $appearance = Appearance::settings();
        require RIFFSIDE_ACCOUNT_DIR . 'templates/admin-navigation.php';
    }
}
