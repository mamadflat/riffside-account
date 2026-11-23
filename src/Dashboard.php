<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

/** Display adapter only. Customer Club owns all reward rules and ledger writes. */
final class Dashboard {
    public static function boot() {
        add_action('woocommerce_edit_account_form', [self::class, 'phone_field']);
        add_action('woocommerce_save_account_details_errors', [self::class, 'validate_phone']);
        add_action('woocommerce_save_account_details', [self::class, 'save_phone']);
    }

    public static function phone_field() {
        $phone = get_user_meta(get_current_user_id(), 'billing_phone', true);
        echo '<p class="woocommerce-form-row form-row form-row-wide"><label for="ra_account_phone">شماره تلفن</label>'
            . '<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" id="ra_account_phone" name="ra_account_phone" value="'
            . esc_attr($phone) . '" autocomplete="tel" dir="ltr" maxlength="32"></p>'; // rtl-ignore: phone input.
    }

    public static function validate_phone($errors) {
        if (!isset($_POST['ra_account_phone'])) return;
        $raw = $_POST['ra_account_phone'];
        if (!is_string($raw) || strlen(wp_unslash($raw)) > 64
            || (trim($raw) !== '' && !preg_match('/^\+?[0-9][0-9 ().-]{4,30}$/D', self::submitted_phone()))) {
            $errors->add('ra_invalid_phone', 'شماره تلفن را درست وارد کن.');
        }
    }

    public static function save_phone($user_id) {
        if (isset($_POST['ra_account_phone']) && is_string($_POST['ra_account_phone']) && (int) $user_id === get_current_user_id()) {
            $customer = new \WC_Customer($user_id);
            $customer->set_billing_phone(self::submitted_phone());
            $customer->save();
        }
    }

    private static function submitted_phone() {
        return trim(strtr(wp_unslash($_POST['ra_account_phone']), [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9', // rtl-ignore: normalize pasted digits.
        ]));
    }

    public static function shortcuts(array $menu) {
        $license = Appearance::settings()['license_endpoint'];
        return array_intersect_key([
            $license => ['plugins', wp_strip_all_tags($menu[$license] ?? 'لایسنس‌ها')],
            'downloads' => ['download', 'دانلودها'],
            Plugin::support_endpoint() => ['ticket', 'پشتیبانی'],
        ], $menu);
    }

    public static function data() {
        $user=wp_get_current_user();
        $active=wc_get_orders(['customer_id'=>$user->ID,'status'=>['wc-pending','wc-on-hold','wc-processing'],'limit'=>2,'paginate'=>true,'orderby'=>'date','order'=>'DESC']);
        $total = wc_get_customer_order_count($user->ID);
        $recent = !$active->orders && $total
            ? wc_get_orders(['customer_id' => $user->ID, 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC'])
            : [];
        $club=['available'=>false];
        if(class_exists('\Customer_Club\Dashboard_Data')) {
            $candidate=\Customer_Club\Dashboard_Data::current();
            if(($candidate['version']??0)===1)$club=$candidate;
        } elseif(class_exists('\Customer_Club\Points_Ledger')&&class_exists('\Customer_Club\Coin')&&\Customer_Club\Coin::is_enabled()) {
            $balance=\Customer_Club\Points_Ledger::balance($user->ID);
            $club=['available'=>true,'balance'=>$balance,'unit'=>\Customer_Club\Coin::name(),'credit'=>\Customer_Club\Reward_Display::equivalent_value(max(0,$balance)),'review'=>false,'referral'=>false,'activities'=>null];
            $club['credit_amount']=\Customer_Club\Coin::value_of(max(0,$balance));$club['currency']=get_woocommerce_currency_symbol();
        }
        return ['user'=>$user,'orders'=>$active->orders,'recent'=>$recent,'active_count'=>$active->total,'order_count'=>$total,'club'=>$club];
    }
    public static function digits($text) {
        return strtr(html_entity_decode((string)$text,ENT_QUOTES,'UTF-8'),['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
    }
    public static function order_hint($order) {
        return ['pending'=>'برای ثبت نهایی، پرداخت سفارش را کامل کن.','on-hold'=>'سفارش در انتظار تأیید فروشگاه است.','processing'=>'سفارش ثبت شده و در حال پردازش است.'][$order->get_status()]??'';
    }
}
