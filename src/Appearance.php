<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class Appearance {
    const OPTION = 'riffside_account_settings';
    public static function presets() {
        $base=json_decode(file_get_contents(RIFFSIDE_ACCOUNT_DIR . 'assets/presets.json'), true);
        $presets=apply_filters('riffside_account_appearance_presets',$base);
        return is_array($presets)?array_merge($presets,['riffside'=>$base['riffside']]):$base;
    }
    public static function defaults() {
        return ['theme'=>'riffside','mode'=>'both','default_mode'=>'light','features'=>['support'=>false,'licenses'=>false],'license_endpoint'=>'licenses','palettes'=>self::presets()['riffside']['palettes']];
    }
    public static function sanitize($input) {
        $input=is_array($input)?$input:[];$out=self::defaults();
        foreach($out['features'] as $feature=>$default)$out['features'][$feature]=isset($input['features'][$feature])&&in_array($input['features'][$feature],[true,1,'1','on'],true);
        if(isset($input['license_endpoint'])&&is_string($input['license_endpoint'])&&preg_match('/^[a-z][a-z0-9-]{1,49}$/D',$input['license_endpoint'])&&!in_array($input['license_endpoint'],['dashboard','orders','view-order','downloads','edit-account','edit-address','customer-logout','payment-methods','add-payment-method','delete-payment-method','set-default-payment-method','lost-password','ra-support','tickets','customer-club','customer-club-transactions','customer-club-referral'],true))$out['license_endpoint']=$input['license_endpoint'];
        if(isset($input['theme']) && is_string($input['theme']) && isset(self::presets()[$input['theme']]))$out['theme']=$input['theme'];
        foreach(['mode'=>['light','dark','both'],'default_mode'=>['light','dark']] as $key=>$allowed)if(isset($input[$key])&&in_array($input[$key],$allowed,true))$out[$key]=$input[$key];
        $palettes=isset($input['palettes'])&&is_array($input['palettes'])?$input['palettes']:[];
        foreach($out['palettes'] as $mode=>$palette){$submitted=isset($palettes[$mode])&&is_array($palettes[$mode])?$palettes[$mode]:[];foreach($palette as $key=>$default){$value=$submitted[$key]??$default;if($key==='button_radius')$out['palettes'][$mode][$key]=is_numeric($value)?max(0,min(24,(int)$value)):$default;elseif(is_string($value)&&preg_match('/^#[0-9a-f]{6}$/iD',$value))$out['palettes'][$mode][$key]=strtolower($value);}}
        return $out;
    }
    public static function settings(){return self::sanitize(get_option(self::OPTION,[]));}
    public static function enabled($feature){return !empty(self::settings()['features'][$feature]);}
    public static function css() {
        $s=self::settings();$initial=$s['mode']==='both'?$s['default_mode']:$s['mode'];$css='';
        foreach(['light','dark'] as $mode){
            $selector='.riffside-account[data-ra-theme="'.$mode.'"]';
            if($mode===$initial)$selector.=',.riffside-account:not([data-ra-theme])';
            $css.=$selector.'{color-scheme:'.$mode.';';
            foreach($s['palettes'][$mode] as $key=>$value)$css.='--ra-'.str_replace('_','-',$key).':'.$value.($key==='button_radius'?'px':'').';';
            $css.='}';
        }
        return apply_filters('riffside_account_appearance_css',$css,$s);
    }
    public static function register() {
        register_setting('riffside_account',self::OPTION,['type'=>'array','sanitize_callback'=>[__CLASS__,'sanitize'],'show_in_rest'=>false]);
        add_filter('option_page_capability_riffside_account',static fn()=>'manage_woocommerce');
    }
    public static function admin_assets($hook) {
        if(strpos($hook,'riffside-account')===false)return;
        wp_enqueue_style('riffside-account-admin',plugins_url('assets/admin.css',RIFFSIDE_ACCOUNT_FILE),[],RIFFSIDE_ACCOUNT_VERSION);
        wp_enqueue_script('riffside-account-admin',plugins_url('assets/admin.js',RIFFSIDE_ACCOUNT_FILE),[],RIFFSIDE_ACCOUNT_VERSION,true);
        wp_localize_script('riffside-account-admin','raPresets',self::presets());
    }
    public static function render() {
        if(!current_user_can('manage_woocommerce'))return;
        require RIFFSIDE_ACCOUNT_DIR.'templates/admin-settings.php';
    }
}

