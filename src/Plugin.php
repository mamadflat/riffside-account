<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class Plugin {
    public static function boot() {
        if(!class_exists('WooCommerce')){add_action('admin_notices',static function(){echo '<div class="notice notice-error"><p>پنل ریف‌ساید به ووکامرس فعال نیاز دارد.</p></div>';});return;}
        add_action('init',[__CLASS__,'endpoints']);
        add_filter('woocommerce_get_query_vars', static function ($vars) {
            if (!Modules::legacy_portal_active()) {
                $endpoint = Modules::support_endpoint();
                $vars[$endpoint] = $endpoint;
            }
            return $vars;
        });
        add_action('wp', [self::class, 'remove_duplicate_club_widget'], 99);
        add_filter('wc_get_template',[__CLASS__,'template'],999,5);
        add_filter('woocommerce_my_account_my_orders_query',[Orders::class,'query'],999);
        add_filter('template_include',static function($template){return is_account_page()&&is_user_logged_in()?RIFFSIDE_ACCOUNT_DIR.'templates/page-account.php':$template;},999);
        add_filter('show_admin_bar',static function($show){return is_account_page()&&is_user_logged_in()?false:$show;});
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],999);
        add_filter('body_class',static function($classes){if(is_account_page()&&is_user_logged_in()){$classes[]='riffside-account-page';}return $classes;},999);
        add_action('template_redirect',static function(){if(is_account_page()){if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);nocache_headers();header('X-Robots-Tag: noindex, nofollow',true);}});
        add_action('admin_init',[Appearance::class,'register']);
        add_action('admin_enqueue_scripts',[Appearance::class,'admin_assets']);
        add_action('admin_menu', static function () {
            $slug = 'riffside-account';
            $title = 'پنل کاربری ریف‌ساید';
            $label = 'پنل ریف‌ساید';
            add_menu_page($title, $label, 'manage_woocommerce', $slug, [Appearance::class, 'render'], 'dashicons-id-alt', 57);
            add_submenu_page($slug, $title, $label, 'manage_woocommerce', $slug, '', 0);
        });
        add_action('admin_menu', [Addons::class, 'register_menu']);
        Modules::boot();
        Receipt::boot();
        add_filter('customer_club_appearance_settings',[__CLASS__,'club_appearance']);
        add_filter('customer_club_account_embedded',static function($embedded){return $embedded||(is_account_page()&&is_user_logged_in());});
        Extensions::boot();
    }
    public static function activate() {
        add_option(Appearance::OPTION,Appearance::defaults(),'',false);self::endpoints();Support::types();flush_rewrite_rules(false);
    }
    public static function deactivate() {
        flush_rewrite_rules(false);
    }
    public static function endpoints() {
        if (!Modules::legacy_portal_active()) add_rewrite_endpoint(Modules::support_endpoint(), EP_ROOT | EP_PAGES);
    }
    public static function support_endpoint() {
        return Modules::support_endpoint();
    }
    public static function template($path,$name,$args,$template_path,$default_path){
        if(!is_account_page()||!is_user_logged_in())return $path;
        $map=['myaccount/my-account.php'=>'account.php','myaccount/navigation.php'=>'navigation.php','myaccount/dashboard.php'=>'dashboard.php','myaccount/orders.php'=>'orders.php','myaccount/view-order.php'=>'view-order.php'];
        if(is_wc_endpoint_url('view-order'))$map+=['order/order-details.php'=>'order-details.php','order/order-details-item.php'=>'order-item.php','order/order-details-customer.php'=>'order-customer.php'];
        return isset($map[$name])?RIFFSIDE_ACCOUNT_DIR.'templates/'.$map[$name]:$path;
    }
    public static function remove_duplicate_club_widget() {
        if(!is_account_page()||!is_user_logged_in())return;
        // The dashboard already shows the same live balance and links to the club.
        remove_action('woocommerce_account_dashboard',['Customer_Club\\Menu_Balance','render_widget'],8);
    }
    /** Remove theme account wrappers and duplicate shortcuts before rendering the panel. */
    public static function remove_duplicate_theme_account_ui() {
        $theme_callbacks = [
            'woocommerce_account_navigation' => [
                'woodmart_my_account_wrapp_start',
                'woodmart_before_my_account_navigation',
                'woodmart_after_my_account_navigation',
            ],
            'woocommerce_account_content' => ['woodmart_my_account_wrapp_end'],
            'woocommerce_account_dashboard' => [
                'woodmart_my_account_links',
                'flatsome_my_account_dashboard',
            ],
        ];

        foreach ($theme_callbacks as $hook => $callbacks) {
            foreach ($callbacks as $callback) {
                $priority = has_action($hook, $callback);
                if ($priority !== false) {
                    remove_action($hook, $callback, $priority);
                }
            }
        }
    }
    public static function assets(){
        if(!is_account_page()||!is_user_logged_in())return;
        if(!wp_style_is('riffside-ui-v1','registered'))wp_register_style('riffside-ui-v1',plugins_url('assets/shared/riffside-ui/foundations.css',RIFFSIDE_ACCOUNT_FILE),[],'1.0.1');
        wp_enqueue_style('riffside-account',plugins_url('assets/account.css',RIFFSIDE_ACCOUNT_FILE),['riffside-ui-v1'],RIFFSIDE_ACCOUNT_VERSION);
        wp_add_inline_style('riffside-account',Appearance::css());
        if(!is_wc_endpoint_url())wp_enqueue_style('riffside-account-home',plugins_url('assets/home.css',RIFFSIDE_ACCOUNT_FILE),['riffside-account'],RIFFSIDE_ACCOUNT_VERSION);
        if(is_wc_endpoint_url('orders')||is_wc_endpoint_url('view-order'))wp_enqueue_style('riffside-account-orders',plugins_url('assets/orders.css',RIFFSIDE_ACCOUNT_FILE),['riffside-account'],RIFFSIDE_ACCOUNT_VERSION);
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
    public static function title(){global $wp;$items=wc_get_account_menu_items();foreach($items as $key=>$label)if(isset($wp->query_vars[$key]))return wp_strip_all_tags($label);if(isset($wp->query_vars['view-order']))return 'جزئیات سفارش';return 'خانه';}
}
