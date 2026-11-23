<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

/** Read-only presentation around WooCommerce's order controller and permissions. */
final class Orders {
    public static function groups() {
        $known=array_keys(wc_get_order_statuses());
        return [
            'current'=>['label'=>'جاری','statuses'=>['wc-pending','wc-on-hold','wc-processing'],'empty'=>'سفارش جاری نداری','hint'=>'سفارش‌های در انتظار پرداخت، تأیید یا پردازش اینجا نمایش داده می‌شوند.'],
            'completed'=>['label'=>'تکمیل‌شده','statuses'=>['wc-completed'],'empty'=>'هنوز سفارش تکمیل‌شده‌ای نداری','hint'=>'پس از تکمیل سفارش در فروشگاه، آن را اینجا می‌بینی.'],
            'other'=>['label'=>'سایر سفارش‌ها','statuses'=>array_values(array_diff($known,['wc-pending','wc-on-hold','wc-processing','wc-completed','wc-checkout-draft'])),'empty'=>'سفارش دیگری ثبت نشده','hint'=>'سفارش‌های لغوشده، ناموفق، مستردشده و وضعیت‌های دیگر اینجا قرار می‌گیرند.'],
        ];
    }
    public static function group($value=null) {
        $value=$value??($_GET['ra_group']??'current'); // Read-only navigation, no nonce needed.
        return is_string($value)&&isset(self::groups()[$value])?$value:'current';
    }
    public static function page($value) { return is_scalar($value)?max(1,min(100000,absint($value))):1; }
    public static function query($args) {
        if(!is_account_page()||!is_user_logged_in()||!is_wc_endpoint_url('orders'))return $args;
        $args['customer']=get_current_user_id();
        $args['customer_id']=get_current_user_id();
        $args['status']=self::groups()[self::group()]['statuses'];
        $args['limit']=10;$args['paginate']=true;$args['return']='objects';
        $args['orderby']='ID';$args['order']='DESC';$args['page']=self::page($args['page']??1);
        return $args;
    }
    public static function counts(array $counts = []) {
        foreach(self::groups() as $key=>$group){
            if (isset($counts[$key])) continue;
            if(!$group['statuses']){$counts[$key]=0;continue;}
            $result=wc_get_orders(['customer_id'=>get_current_user_id(),'status'=>$group['statuses'],'limit'=>1,'page'=>1,'return'=>'ids','paginate'=>true]);
            $counts[$key]=(int)$result->total;
        }
        return $counts;
    }
    public static function tone($status) {
        return match ($status) {
            'completed' => 'success',
            'cancelled', 'failed' => 'danger',
            'pending', 'on-hold' => 'waiting',
            'processing' => 'active',
            default => 'neutral',
        };
    }
    public static function url($group='current',$page=1) {
        return add_query_arg('ra_group',self::group($group),wc_get_endpoint_url('orders',self::page($page),wc_get_page_permalink('myaccount')));
    }
    public static function view_url($order,$page=1) {
        return add_query_arg(['ra_from'=>self::group(),'ra_page'=>self::page($page)],$order->get_view_order_url());
    }
    public static function back_url($order) {
        $default=$order->has_status('completed')?'completed':($order->has_status(['pending','on-hold','processing'])?'current':'other');
        return self::url(self::group($_GET['ra_from']??$default),self::page($_GET['ra_page']??1));
    }
    public static function owns($order) {
        return $order&&get_current_user_id()>0&&(int)$order->get_customer_id()===get_current_user_id()&&current_user_can('view_order',$order->get_id());
    }
    public static function date($date) {
        if(!$date)return 'تاریخ ثبت نشده';
        if(strpos(determine_locale(),'fa')===0&&class_exists('\IntlDateFormatter')){
            try{
                $formatter=new \IntlDateFormatter('fa_IR@calendar=persian',\IntlDateFormatter::MEDIUM,\IntlDateFormatter::NONE,wp_timezone_string(),\IntlDateFormatter::TRADITIONAL,'d MMMM y');
                $value=$formatter->format($date->getTimestamp());
                if($value!==false)return Dashboard::digits($value);
            }catch(\Throwable $error){return Dashboard::digits(wc_format_datetime($date));}
        }
        return Dashboard::digits(wc_format_datetime($date));
    }
    public static function hint($order) {
        return [
            'pending'=>'برای ادامهٔ سفارش، پرداخت را کامل کن.',
            'on-hold'=>'سفارش در انتظار تأیید فروشگاه است.',
            'processing'=>'سفارش ثبت شده و در حال پردازش است.',
            'completed'=>'این سفارش در فروشگاه تکمیل شده است.',
            'cancelled'=>'این سفارش لغو شده است.',
            'failed'=>'پرداخت این سفارش ناموفق بوده است.',
            'refunded'=>'برای این سفارش بازپرداخت ثبت شده است.',
        ][$order->get_status()]??'آخرین وضعیت ثبت‌شدهٔ فروشگاه را در این صفحه می‌بینی.';
    }
    public static function actions($order,$detail=false,$page=1) {
        $actions=wc_get_account_orders_actions($order);
        if($detail)unset($actions['view']);
        elseif(isset($actions['view'])){$actions['view']['url']=self::view_url($order,$page);$actions['view']['name']='جزئیات سفارش';}
        $primary = isset($actions['pay']) ? 'pay' : 'view';
        foreach($actions as $key=>$action){
            $label=$action['aria-label']??($action['name'].'، سفارش '.$order->get_order_number());
            echo '<a class="ra-button '.($key===$primary?'ra-primary ':'').esc_attr(sanitize_html_class($key)).'" href="'.esc_url($action['url']).'" aria-label="'.esc_attr($label).'">'.esc_html($action['name']).'</a>';
        }
    }
}
