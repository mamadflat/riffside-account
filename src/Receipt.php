<?php
namespace Riffside\Account;

defined('ABSPATH') || exit;

/** Presents the verified classic WooCommerce receipt in the account design. */
final class Receipt {
    private const CLUB_CALLBACK = [\Customer_Club\Purchase_Rewards::class, 'render_order_reward'];
    private const ORDER_TABLE_HOOK = 'woocommerce_order_details_after_order_table';

    public static function boot() {
        add_filter('template_include', [self::class, 'page'], 999);
        add_filter('wc_get_template', [self::class, 'template'], 999, 3);
        add_action('wp_enqueue_scripts', [self::class, 'assets']);
        add_action('template_redirect', [self::class, 'private_headers']);
        add_filter('show_admin_bar', static fn ($show) => self::is_receipt() ? false : $show);
        add_filter('pre_get_document_title', static function ($title) {
            return self::is_receipt() ? 'تأیید سفارش | ' . get_bloginfo('name') : $title;
        }, 1000);
    }

    private static function is_receipt() {
        return is_order_received_page() && !has_block('woocommerce/checkout', wc_get_page_id('checkout'));
    }

    public static function page($template) {
        return self::is_receipt() ? RIFFSIDE_ACCOUNT_DIR . 'templates/page-order-received.php' : $template;
    }

    public static function template($path, $name, $args) {
        if ($name !== 'checkout/thankyou.php' || !self::is_receipt()) {
            return $path;
        }

        // WooCommerce passes an order only after verifying the receipt key and customer.
        $order = $args['order'] ?? null;
        if (!($order instanceof \WC_Order) || !$order->is_paid() || $order->has_status(['failed', 'cancelled', 'refunded'])) {
            return $path;
        }

        return RIFFSIDE_ACCOUNT_DIR . 'templates/checkout-thankyou.php';
    }

    public static function assets() {
        if (!self::is_receipt()) {
            return;
        }

        wp_enqueue_style('riffside-account-receipt', plugins_url('assets/receipt.css', RIFFSIDE_ACCOUNT_FILE), [], RIFFSIDE_ACCOUNT_VERSION);
        wp_add_inline_style('riffside-account-receipt', Appearance::css());
    }

    public static function private_headers() {
        if (!self::is_receipt()) {
            return;
        }

        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        nocache_headers();
        header('X-Robots-Tag: noindex, nofollow', true);
    }

    public static function reward(\WC_Order $order) {
        if (!is_callable(self::CLUB_CALLBACK) || has_action(self::ORDER_TABLE_HOOK, self::CLUB_CALLBACK) === false) {
            return '';
        }

        ob_start();
        call_user_func(self::CLUB_CALLBACK, $order);
        return trim(ob_get_clean());
    }

    public static function details(\WC_Order $order, $reward_shown) {
        // Keep third-party thank-you callbacks and move only the Club card above the receipt details.
        $priority = $reward_shown ? has_action(self::ORDER_TABLE_HOOK, self::CLUB_CALLBACK) : false;
        if ($priority !== false) {
            remove_action(self::ORDER_TABLE_HOOK, self::CLUB_CALLBACK, $priority);
        }

        try {
            do_action('woocommerce_thankyou', $order->get_id());
        } finally {
            if ($priority !== false) {
                add_action(self::ORDER_TABLE_HOOK, self::CLUB_CALLBACK, $priority);
            }
        }
    }
}
