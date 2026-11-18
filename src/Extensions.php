<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

/** Versioned, request-local extension registry. Registration is trusted PHP, never request data. */
final class Extensions {
    const API_VERSION = 1;
    private static $items = ['page'=>[], 'widget'=>[], 'order'=>[], 'settings'=>[]];
    private static $initialized = false;

    public static function boot() {
        add_action('init', [__CLASS__, 'initialize'], 5);
        add_filter('woocommerce_get_query_vars', [__CLASS__, 'query_vars'], 90);
        add_filter('woocommerce_account_menu_items', [__CLASS__, 'menu'], 9998);
        add_action('template_redirect', [__CLASS__, 'guard'], 1);
        add_action('woocommerce_view_order', [__CLASS__, 'order_sections'], 5);
    }

    /** Add-ons register on this event; registering twice cannot duplicate output. */
    public static function initialize() {
        if (self::$initialized) return;
        self::$initialized = true;
        do_action('riffside_account_register_extensions', self::API_VERSION);
        foreach (self::$items['page'] as $id => $item) {
            add_rewrite_endpoint($id, EP_ROOT | EP_PAGES);
            add_action('woocommerce_account_'.$id.'_endpoint', static function () use ($id) { self::page($id); });
        }
        // Compare only during administrative requests, not on customer visits.
        if (is_admin() && current_user_can('manage_woocommerce')) {
            $routes = array_keys(self::$items['page']); sort($routes);
            if (get_option('riffside_account_extension_routes', null) !== $routes) {
                flush_rewrite_rules(false);
                update_option('riffside_account_extension_routes', $routes, false);
            }
        }
    }

    /** Return false for invalid/duplicate identifiers or a collision with an existing route. */
    public static function register($kind, $id, array $item) {
        if (!isset(self::$items[$kind]) || !is_string($id) || !preg_match('/^ra-[a-z][a-z0-9-]{2,45}$/D', $id)) return false;
        if (isset(self::$items[$kind][$id]) || in_array($id, ['ra-support'], true)) return false;
        if (!isset($item['title'], $item['render']) || !is_string($item['title']) || !is_callable($item['render'])) return false;
        if (isset($item['permission']) && !is_callable($item['permission'])) return false;
        if (isset($item['enabled']) && !is_bool($item['enabled'])) return false;
        if ($kind === 'page') {
            $existing = apply_filters('woocommerce_get_query_vars', []);
            if (isset($existing[$id]) || in_array($id, $existing, true)) return false;
        }
        self::$items[$kind][$id] = $item + ['enabled'=>true, 'priority'=>50, 'icon'=>'files'];
        return true;
    }

    private static function allowed($item, $context = null) {
        return $item['enabled'] && is_user_logged_in() && (!isset($item['permission']) || (bool)call_user_func($item['permission'], get_current_user_id(), $context));
    }
    private static function sorted($kind) {
        $items = self::$items[$kind];
        uasort($items, static fn($a,$b)=>(int)$a['priority']<=>(int)$b['priority']);
        return $items;
    }
    public static function query_vars($vars) {
        foreach (self::$items['page'] as $id => $item) if (!isset($vars[$id])) $vars[$id]=$id;
        return $vars;
    }
    public static function menu($items) {
        foreach (self::sorted('page') as $id => $item) if (self::allowed($item)) $items[$id]=$item['title'];
        return $items;
    }
    public static function icon($id, $fallback = 'files') { return self::$items['page'][$id]['icon'] ?? $fallback; }
    public static function guard() {
        if (!is_account_page()) return;
        global $wp;
        foreach (self::$items['page'] as $id => $item) if (array_key_exists($id, $wp->query_vars) && !self::allowed($item)) self::denied();
    }
    private static function denied() { nocache_headers(); wp_die('این بخش در دسترس نیست.', 'بخش غیرفعال', ['response'=>404]); }
    public static function page($id) {
        $item=self::$items['page'][$id]??null;
        if (!$item || !self::allowed($item)) { self::denied(); return; }
        self::render($id, $item, null);
    }
    private static function render($id, $item, $context) {
        echo '<section class="ra-card ra-extension" data-ra-extension="'.esc_attr($id).'"><h2>'.esc_html($item['title']).'</h2>';
        call_user_func($item['render'], get_current_user_id(), $context);
        echo '</section>';
    }
    public static function widgets() {
        if (!is_account_page() || !is_user_logged_in()) return;
        echo '<div class="ra-extension-widgets">';
        foreach (self::sorted('widget') as $id=>$item) if (self::allowed($item)) self::render($id,$item,null);
        echo '</div>';
    }
    public static function order_sections($id) {
        $order=wc_get_order($id);
        if (!is_account_page() || !is_user_logged_in() || !$order || (int)$order->get_customer_id()!==get_current_user_id()) return;
        foreach (self::sorted('order') as $key=>$item) if (self::allowed($item,$order)) self::render($key,$item,$order);
    }
    public static function settings_sections() {
        if (!current_user_can('manage_woocommerce')) return;
        foreach (self::sorted('settings') as $id=>$item) if (self::allowed($item)) self::render($id,$item,null);
    }
}
