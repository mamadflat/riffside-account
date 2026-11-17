<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

/** Display adapter only. Customer Club owns all reward rules and ledger writes. */
final class Dashboard {
    public static function data() {
        $user=wp_get_current_user();
        $active=wc_get_orders(['customer_id'=>$user->ID,'status'=>['wc-pending','wc-on-hold','wc-processing'],'limit'=>2,'paginate'=>true,'orderby'=>'date','order'=>'DESC']);
        $club=['available'=>false];
        if(class_exists('\Customer_Club\Dashboard_Data')) {
            $candidate=\Customer_Club\Dashboard_Data::current();
            if(($candidate['version']??0)===1)$club=$candidate;
        } elseif(class_exists('\Customer_Club\Points_Ledger')&&class_exists('\Customer_Club\Coin')&&\Customer_Club\Coin::is_enabled()) {
            $balance=\Customer_Club\Points_Ledger::balance($user->ID);
            $club=['available'=>true,'balance'=>$balance,'unit'=>\Customer_Club\Coin::name(),'credit'=>\Customer_Club\Reward_Display::equivalent_value(max(0,$balance)),'review'=>false,'referral'=>false,'activities'=>null];
            $club['credit_amount']=\Customer_Club\Coin::value_of(max(0,$balance));$club['currency']=get_woocommerce_currency_symbol();
        }
        return ['user'=>$user,'orders'=>$active->orders,'active_count'=>$active->total,'order_count'=>wc_get_customer_order_count($user->ID),'club'=>$club];
    }
    public static function digits($text) {
        return strtr(html_entity_decode((string)$text,ENT_QUOTES,'UTF-8'),['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    }
    public static function order_hint($order) {
        return ['pending'=>'برای ثبت نهایی، پرداخت سفارش را کامل کن.','on-hold'=>'سفارش در انتظار تأیید فروشگاه است.','processing'=>'سفارش ثبت شده و در حال پردازش است.'][$order->get_status()]??'';
    }
}
