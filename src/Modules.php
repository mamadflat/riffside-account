<?php
namespace Riffside\Account;

defined('ABSPATH') || exit;

final class Modules {
    public static function boot() {
        PortalTickets::remember_recipient();
        if (!self::legacy_portal_active() && PortalTickets::selected()) PortalTickets::boot();
        else Support::boot();
        add_filter('woocommerce_account_menu_items', [self::class, 'menu'], 9999);
        add_action('template_redirect', [self::class, 'guard'], 0);
        add_action('wp', [self::class, 'license_order_visibility'], 99);
    }

    public static function legacy_portal_active() {
        return class_exists('Riffside_Portal');
    }

    public static function uses_portal() {
        return self::legacy_portal_active() || PortalTickets::selected();
    }

    public static function support_endpoint() {
        return self::uses_portal() ? 'tickets' : 'ra-support';
    }

    public static function prepaid_license_provider_available() {
        return class_exists(\YHLM\Customer::class);
    }

    private static function issuer_owns_endpoint($endpoint) {
        return $endpoint === 'licenses' && false !== has_action('woocommerce_account_licenses_endpoint', 'ahrari_customer_licenses');
    }

    public static function menu($items) {
        if (isset($items['dashboard'])) $items['dashboard'] = 'خانه';

        $logout = $items['customer-logout'] ?? 'خروج';
        unset($items['customer-logout']);

        $settings = Appearance::settings();
        if ($settings['features']['support']) {
            $items[self::support_endpoint()] = 'پشتیبانی';
        } else {
            unset($items['ra-support'], $items['tickets']);
        }

        if (!$settings['features']['licenses']) {
            foreach (['licenses', $settings['license_endpoint']] as $endpoint) {
                if (!self::issuer_owns_endpoint($endpoint)) unset($items[$endpoint]);
            }
        }

        $items['customer-logout'] = $logout;
        return $items;
    }

    public static function guard() {
        if (!is_account_page()) return;

        global $wp;
        $settings = Appearance::settings();
        $blocked = [];
        if (!$settings['features']['support']) {
            $blocked = ['ra-support', 'tickets'];
        }
        if (!$settings['features']['licenses']) {
            foreach (['licenses', $settings['license_endpoint']] as $endpoint) {
                if (!self::issuer_owns_endpoint($endpoint)) $blocked[] = $endpoint;
            }
        }

        foreach ($blocked as $endpoint) {
            if (!array_key_exists($endpoint, $wp->query_vars)) continue;
            nocache_headers();
            wp_die('این بخش در این فروشگاه فعال نیست.', 'بخش غیرفعال', ['response' => 404, 'back_link' => true]);
        }
    }

    public static function license_order_visibility() {
        if (is_account_page() && !Appearance::enabled('licenses') && self::prepaid_license_provider_available()) {
            remove_action('woocommerce_account_licenses_endpoint', [\YHLM\Customer::class, 'account']);
            remove_action('woocommerce_order_details_after_order_table', [\YHLM\Customer::class, 'order']);
        }
    }
}
