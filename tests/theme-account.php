<?php
define('ABSPATH', __DIR__);

$callbacks = [
    'woocommerce_account_navigation' => [
        'woodmart_my_account_wrapp_start' => 0,
        'woodmart_before_my_account_navigation' => 5,
        'woocommerce_account_navigation' => 10,
        'woodmart_after_my_account_navigation' => 30,
    ],
    'woocommerce_account_content' => [
        'woocommerce_output_all_notices' => 5,
        'woocommerce_account_content' => 10,
        'woodmart_my_account_wrapp_end' => 10000,
    ],
    'woocommerce_account_dashboard' => [
        'woodmart_my_account_links' => 10,
        'flatsome_my_account_dashboard' => 10,
        'another_plugin_dashboard' => 10,
    ],
    'woocommerce_account_menu_items' => ['woodmart_my_account_navigation' => 15],
];

function has_action($hook, $callback) {
    return $GLOBALS['callbacks'][$hook][$callback] ?? false;
}

function remove_action($hook, $callback, $priority) {
    if (has_action($hook, $callback) === $priority) {
        unset($GLOBALS['callbacks'][$hook][$callback]);
    }
}

require dirname(__DIR__).'/src/Plugin.php';
\Riffside\Account\Plugin::remove_duplicate_theme_account_ui();

$expected = [
    'woocommerce_account_navigation' => ['woocommerce_account_navigation' => 10],
    'woocommerce_account_content' => [
        'woocommerce_output_all_notices' => 5,
        'woocommerce_account_content' => 10,
    ],
    'woocommerce_account_dashboard' => ['another_plugin_dashboard' => 10],
    'woocommerce_account_menu_items' => ['woodmart_my_account_navigation' => 15],
];
if ($callbacks !== $expected) {
    throw new RuntimeException('Theme wrappers and shortcuts must be removed without changing account routes or third-party callbacks.');
}
echo "Theme presentation callbacks removed; WooCommerce and third-party callbacks preserved.\n";
