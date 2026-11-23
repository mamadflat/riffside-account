<?php
namespace Riffside\Account {
    final class Appearance {
        public static function settings() { return ['license_endpoint' => 'vendor-license']; }
    }
}

namespace {
    define('ABSPATH', __DIR__);
    require dirname(__DIR__) . '/src/Sections.php';

    function WC() {
        return (object) ['query' => new class {
            public function get_current_endpoint() { return $GLOBALS['current_endpoint']; }
        }];
    }

    function has_action($hook) {
        return isset($GLOBALS['account_actions'][$hook]) ? 10 : false;
    }

    $cases = [
        ['', [], [], ''],
        ['customer-club', ['customer-club' => ''], [], ''],
        ['customer-club-transactions', [], [], ''],
        ['', ['customer-club-referral' => ''], ['woocommerce_account_customer-club-referral_endpoint' => true], ''],
        ['', ['page_id' => 5, 'licenses' => ''], ['woocommerce_account_licenses_endpoint' => true], 'licenses'],
        ['vendor-license', [], [], 'licenses'],
        ['ra-support', [], [], 'tickets'],
        ['tickets', [], [], 'tickets'],
        ['orders', [], [], 'orders'],
        ['view-order', [], [], 'view-order'],
        ['payment-methods', [], [], 'payment-methods'],
        ['add-payment-method', [], [], 'payment-methods'],
        ['ra-tab-23', [], [], 'extension'],
        ['', ['unhandled' => ''], [], ''],
    ];

    foreach ($cases as [$current_endpoint, $query_vars, $account_actions, $expected]) {
        $wp = (object) ['query_vars' => $query_vars];
        $actual = \Riffside\Account\Sections::current();
        if ($actual !== $expected) {
            throw new \RuntimeException('Unexpected section for ' . ($current_endpoint ?: implode(',', array_keys($query_vars))));
        }
    }

    echo "Account section styles are routed correctly, including the Customer Club exclusion.\n";
}
