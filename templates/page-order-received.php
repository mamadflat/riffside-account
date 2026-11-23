<?php
defined('ABSPATH') || exit;

use Riffside\Account\Appearance;

$settings = Appearance::settings();
$mode = $settings['mode'] === 'both' ? $settings['default_mode'] : $settings['mode'];
$content = \WC_Shortcodes::checkout([]);
$shop_url = wc_get_page_permalink('shop');
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php wp_head(); ?>
</head>
<body <?php body_class('riffside-account ra-receipt-document'); ?> data-ra-policy="<?php echo esc_attr($settings['mode']); ?>" data-ra-theme="<?php echo esc_attr($mode); ?>">
<script>(function(root){if(root.dataset.raPolicy!=='both')return;try{var mode=localStorage.getItem('riffside-account-mode');if(mode==='light'||mode==='dark')root.dataset.raTheme=mode;}catch(error){}})(document.body);</script>
<?php wp_body_open(); ?>
<main id="main" class="ra-receipt">
    <header class="ra-receipt__nav">
        <a class="ra-receipt__store" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
        <?php if ($shop_url) : ?>
            <a href="<?php echo esc_url($shop_url); ?>">بازگشت به فروشگاه</a>
        <?php endif; ?>
    </header>
    <?php echo $content; // WooCommerce renders its verified receipt, login or verification form. ?>
</main>
<?php wp_footer(); ?>
</body>
</html>
