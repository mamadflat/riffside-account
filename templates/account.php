<?php
defined('ABSPATH') || exit;
use Riffside\Account\Plugin;
use Riffside\Account\Appearance;
$settings=Appearance::settings();
$user=wp_get_current_user();
$dashboard = !is_wc_endpoint_url();
?>
<div class="riffside-account" dir="rtl" data-cc-no-menu-balance data-ra-policy="<?php echo esc_attr($settings['mode']); ?>" data-ra-theme="<?php echo esc_attr($settings['mode']==='both'?$settings['default_mode']:$settings['mode']); ?>">
    <script>(function(root){if(root.dataset.raPolicy!=='both')return;try{var mode=localStorage.getItem('riffside-account-mode');if(mode==='light'||mode==='dark')root.dataset.raTheme=mode;}catch(error){}})(document.currentScript.parentElement);</script>
    <a class="ra-skip" href="#ra-content">رفتن به محتوای حساب</a>
    <aside class="ra-sidebar" id="ra-sidebar" aria-label="منوی حساب کاربری">
        <div class="ra-brand"><span class="ra-brand-mark"><?php echo Plugin::icon('store'); ?></span><div><strong><?php echo esc_html(get_bloginfo('name')); ?></strong><small>حساب کاربری من</small></div><button type="button" class="ra-menu-close" data-ra-menu-close aria-label="بستن منوی حساب">×</button></div>
        <?php do_action('woocommerce_account_navigation'); ?>
        <div class="ra-sidebar-profile"><?php echo Plugin::icon('account'); ?><div><strong><bdi><?php echo esc_html($user->display_name); ?></bdi></strong><small>حساب شما</small></div></div>
    </aside>
    <button type="button" class="ra-menu-backdrop" data-ra-menu-close tabindex="-1" aria-label="بستن منوی حساب" hidden></button>
    <div class="ra-workspace">
        <header class="ra-heading<?php echo $dashboard ? ' ra-heading--dashboard' : ''; ?>">
            <div class="ra-heading-title">
                <button type="button" class="ra-menu-toggle" data-ra-menu-toggle aria-controls="ra-sidebar" aria-expanded="false" aria-label="باز کردن منوی حساب"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                <?php if (!$dashboard) : ?><span><small>حساب کاربری</small><strong><?php echo esc_html(Plugin::title()); ?></strong></span><?php endif; ?>
            </div>
            <div class="ra-heading-actions">
                <?php if ($settings['mode'] === 'both') : ?><button type="button" data-ra-theme-toggle aria-pressed="false">تغییر تم</button><?php endif; ?>
                <a class="ra-back-to-store" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">بازگشت به فروشگاه <?php echo Plugin::icon('arrow-left'); ?></a>
            </div>
        </header>
        <main class="woocommerce-MyAccount-content ra-content" id="ra-content" tabindex="-1"><?php do_action('woocommerce_account_content'); ?></main>
    </div>
</div>
