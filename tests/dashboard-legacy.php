<?php
namespace Customer_Club {
    class Points_Ledger {
        public static function balance($id) { if($id!==17)throw new \RuntimeException('Wrong customer');return -2; }
    }
    class Coin {
        public static function is_enabled(){return true;}
        public static function name(){return 'کوین';}
        public static function value_of($balance){return $balance*5000;}
    }
    class Reward_Display {
        public static function equivalent_value($balance){return (string)Coin::value_of($balance);}
    }
}
namespace {
    define('ABSPATH',__DIR__);
    function wp_get_current_user(){return (object)['ID'=>17];}
    function wc_get_orders($args){return (object)['orders'=>[],'total'=>0];}
    function wc_get_customer_order_count($id){return 0;}
    function get_woocommerce_currency_symbol(){return 'تومان';}
    require dirname(__DIR__).'/src/Dashboard.php';
    $club=\Riffside\Account\Dashboard::data()['club'];
    if(!$club['available']||$club['balance']!==-2||$club['credit_amount']!==0)throw new \RuntimeException('Legacy balance or nonnegative credit failed');
    if($club['activities']!==null||$club['review']!==false||$club['referral']!==false)throw new \RuntimeException('Unsupported legacy data must not become fabricated empty history or rewards');
    echo "Legacy Club fallback preserves balance and hides unsupported activity.\n";
}
