<?php
// Dedicated account document using WooCommerce's existing account controller.
// Theme header/footer templates are never rendered; WordPress asset hooks remain.
defined('ABSPATH') || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <script>document.documentElement.classList.add('ra-js');</script>
    <?php wp_head(); ?>
</head>
<body <?php body_class('ra-dashboard-document'); ?>>
<?php wp_body_open(); ?>
<?php echo \WC_Shortcodes::my_account([]); // Core controller handles endpoints, notices and account access. ?>
<?php wp_footer(); ?>
</body>
</html>
