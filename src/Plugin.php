<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class Plugin {
    public static function boot() {
        if(!class_exists('WooCommerce')){add_action('admin_notices',static function(){echo '<div class="notice notice-error"><p>پنل ریف‌ساید به ووکامرس فعال نیاز دارد.</p></div>';});return;}
        add_action('init',[__CLASS__,'endpoints']);
        add_filter('woocommerce_get_query_vars',static function($vars){if(!class_exists('Riffside_Portal'))$vars['ra-support']='ra-support';return $vars;});
        add_action('wp',[__CLASS__,'legacy'],99);
        add_filter('wc_get_template',[__CLASS__,'template'],999,5);
        add_filter('template_include',static function($template){return is_account_page()&&is_user_logged_in()?RIFFSIDE_ACCOUNT_DIR.'templates/page-account.php':$template;},999);
        add_filter('show_admin_bar',static function($show){return is_account_page()&&is_user_logged_in()?false:$show;});
        add_filter('woocommerce_account_menu_items',[__CLASS__,'menu'],9999);
        add_action('template_redirect',[__CLASS__,'guard_modules'],0);
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],999);
        add_filter('body_class',static function($classes){if(is_account_page()&&is_user_logged_in()){$classes=array_diff($classes,['valabi-account-panel','va-account']);$classes[]='riffside-account-page';}return $classes;},999);
        add_action('template_redirect',static function(){if(is_account_page()){if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);nocache_headers();header('X-Robots-Tag: noindex, nofollow',true);}});
        add_action('admin_init',[Appearance::class,'register']);
        add_action('admin_enqueue_scripts',[Appearance::class,'admin_assets']);
        add_action('admin_menu',static function(){add_menu_page('پنل کاربری ریف‌ساید','پنل ریف‌ساید','manage_woocommerce','riffside-account',[Appearance::class,'render'],'dashicons-id-alt',57);});
        Support::boot();
        add_filter('customer_club_appearance_settings',[__CLASS__,'club_appearance']);
        add_filter('customer_club_account_embedded',static function($embedded){return $embedded||(is_account_page()&&is_user_logged_in());});
        add_action('woocommerce_view_order',[__CLASS__,'tracking'],5);
    }
    public static function activate() {
        add_option(Appearance::OPTION,Appearance::defaults(),'',false);self::endpoints();Support::types();flush_rewrite_rules(false);
    }
    public static function endpoints(){if(!class_exists('Riffside_Portal'))add_rewrite_endpoint('ra-support',EP_ROOT|EP_PAGES);}
    public static function support_endpoint(){return class_exists('Riffside_Portal')?'tickets':'ra-support';}
    public static function menu($items){
        if(isset($items['dashboard']))$items['dashboard']='خانه';
        $logout=$items['customer-logout']??'خروج';unset($items['customer-logout']);
        if(Appearance::enabled('support'))$items[self::support_endpoint()]='پشتیبانی';else unset($items['ra-support'],$items['tickets']);
        if(!Appearance::enabled('licenses')){unset($items['licenses'],$items[Appearance::settings()['license_endpoint']]);}
        $items['customer-logout']=$logout;return $items;
    }
    public static function guard_modules(){
        if(!is_account_page())return;
        global $wp;
        $disabled=[];
        if(!Appearance::enabled('support'))$disabled=array_merge($disabled,['ra-support','tickets']);
        if(!Appearance::enabled('licenses'))$disabled=array_merge($disabled,['licenses',Appearance::settings()['license_endpoint']]);
        foreach($disabled as $endpoint)if(array_key_exists($endpoint,$wp->query_vars)){nocache_headers();wp_die('این بخش در این فروشگاه فعال نیست.','بخش غیرفعال',['response'=>404,'back_link'=>true]);}
    }
    public static function template($path,$name,$args,$template_path,$default_path){
        if(!is_account_page()||!is_user_logged_in())return $path;
        $map=['myaccount/my-account.php'=>'account.php','myaccount/navigation.php'=>'navigation.php','myaccount/dashboard.php'=>'dashboard.php'];
        return isset($map[$name])?RIFFSIDE_ACCOUNT_DIR.'templates/'.$map[$name]:$path;
    }
    public static function legacy(){
        if(!is_account_page()||!is_user_logged_in())return;
        // The dashboard already shows the same live balance and links to the club.
        remove_action('woocommerce_account_dashboard',['Customer_Club\\Menu_Balance','render_widget'],8);
        if(!Appearance::enabled('licenses'))remove_action('woocommerce_order_details_after_order_table',['YHLM\\Customer','order']);
        // Retain Valabi's operational hooks, settings, registry, birthday and receipts.
        // Only remove its account presentation; deactivating this plugin restores it.
        $remove=['MBN_Frontend'=>['render','enqueue','body_class'],'Valabi_Account_Page_Header'=>['render'],'Valabi_Account_Flatsome'=>['render_home_button'],'Valabi_Account_Order'=>['override_view_order_template'],'Valabi_Account_Tabs'=>['orders_toolbar','downloads_empty','addresses_empty']];
        global $wp_filter;
        foreach($wp_filter as $hook=>$object)foreach($object->callbacks as $priority=>$callbacks)foreach($callbacks as $callback){$fn=$callback['function'];if(is_array($fn)&&is_object($fn[0])&&isset($remove[get_class($fn[0])])&&in_array($fn[1],$remove[get_class($fn[0])],true))remove_filter($hook,$fn,$priority);}
    }
    public static function assets(){
        if(!is_account_page()||!is_user_logged_in())return;
        wp_dequeue_style('valabi-account');
        wp_dequeue_script('yhm-header');
        if(!wp_style_is('riffside-ui-v1','registered'))wp_register_style('riffside-ui-v1',plugins_url('assets/shared/riffside-ui/foundations.css',RIFFSIDE_ACCOUNT_FILE),[],'1.0.0');
        wp_enqueue_style('riffside-account',plugins_url('assets/account.css',RIFFSIDE_ACCOUNT_FILE),['riffside-ui-v1'],RIFFSIDE_ACCOUNT_VERSION);
        wp_add_inline_style('riffside-account',Appearance::css());
        if(!is_wc_endpoint_url())wp_enqueue_style('riffside-account-home',plugins_url('assets/home.css',RIFFSIDE_ACCOUNT_FILE),['riffside-account'],RIFFSIDE_ACCOUNT_VERSION);
        wp_enqueue_script('riffside-account',plugins_url('assets/account.js',RIFFSIDE_ACCOUNT_FILE),[],RIFFSIDE_ACCOUNT_VERSION,true);
        $s=Appearance::settings();wp_localize_script('riffside-account','riffsideAccount',['mode'=>$s['mode'],'defaultMode'=>$s['default_mode']]);
    }
    public static function club_appearance($settings){
        if(is_admin()||!is_account_page()||!is_user_logged_in())return $settings;
        $panel=Appearance::settings();
        foreach(['theme','mode','default_mode','palettes'] as $key)$settings[$key]=$panel[$key];
        return $settings;
    }
    public static function icon($name){$allowed=['account','services','store','customer-club','ticket','download','plugins','corporate','files','logout','arrow-left','check','clock'];if(!in_array($name,$allowed,true))$name='files';return '<svg aria-hidden="true" focusable="false"><use href="'.esc_url(plugins_url('assets/icons.svg',RIFFSIDE_ACCOUNT_FILE)).'#ri-'.$name.'"></use></svg>';}
    public static function url($endpoint='dashboard'){return $endpoint==='customer-logout'?wc_logout_url():wc_get_account_endpoint_url($endpoint);}
    public static function number($value){return strtr(number_format_i18n($value),['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);}
    public static function tracking($order_id){
        $order=wc_get_order($order_id);if(!$order||(int)$order->get_customer_id()!==get_current_user_id())return;
        $code=(string)$order->get_meta('_valabi_account_tracking_code');if(!$code)$code=(string)$order->get_meta('post_barcode');
        if(!$code||!preg_match('/^[0-9]{10,30}$/D',$code))return;
        echo '<section class="ra-card"><h3>رهگیری سفارش</h3><p>کد رهگیری: <bdi dir="ltr">'.esc_html($code).'</bdi></p><button type="button" data-ra-copy="'.esc_attr($code).'">کپی کد رهگیری</button><a class="ra-button" href="https://tracking.post.ir/" target="_blank" rel="noopener noreferrer">ورود به سامانهٔ پست</a><span data-ra-copy-status role="status"></span></section>';
    }
    public static function title(){global $wp;$items=wc_get_account_menu_items();foreach($items as $key=>$label)if(isset($wp->query_vars[$key]))return wp_strip_all_tags($label);if(isset($wp->query_vars['view-order']))return 'جزئیات سفارش';return 'خانه';}
}
