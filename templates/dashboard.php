<?php
defined('ABSPATH') || exit;

use Riffside\Account\Dashboard;
use Riffside\Account\Extensions;
use Riffside\Account\Plugin;

$data = Dashboard::data();
$orders = $data['orders'] ?: $data['recent'];
$club = $data['club'];
$menu = wc_get_account_menu_items();
$shortcuts = [
    'licenses' => ['plugins', 'لایسنس‌ها و دانلودها'],
    Plugin::support_endpoint() => ['ticket', 'پشتیبانی'],
];
$shortcuts = array_intersect_key($shortcuts, $menu);
$show_club = !empty($club['available']) && isset($menu['customer-club'])
    && ((int) $club['balance'] !== 0 || (float) $club['credit_amount'] > 0);
?>
<div class="ra-home" data-ra-home>
    <section class="ra-home-card ra-current-orders" id="ra-current-orders" aria-labelledby="ra-orders-title" data-ra-widget="current-orders">
        <header class="ra-home-section-heading">
            <h1 id="ra-orders-title">سفارش‌ها</h1>
            <?php if ($data['order_count']) : ?>
                <a class="ra-home-text-link" href="<?php echo esc_url(Plugin::url('orders')); ?>">همهٔ سفارش‌ها <?php echo Plugin::icon('arrow-left'); ?></a>
            <?php endif; ?>
        </header>
        <?php if (!$orders) : ?>
            <div class="ra-home-empty">
                <p><?php echo $data['order_count'] ? 'سفارش جاری نداری.' : 'هنوز سفارشی ثبت نشده است.'; ?></p>
                <?php if (!$data['order_count']) : ?>
                    <a class="ra-home-text-link" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">دیدن محصولات <?php echo Plugin::icon('arrow-left'); ?></a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <ul class="ra-current-list">
                <?php foreach ($orders as $order) :
                    $needs_payment = $order->needs_payment();
                    $action_url = $needs_payment ? $order->get_checkout_payment_url() : $order->get_view_order_url();
                    $items = array_slice($order->get_items(), 0, 2);
                    ?>
                    <li class="ra-current-order">
                        <div class="ra-home-order-info">
                            <div class="ra-home-order-title">
                                <strong>سفارش <bdi dir="ltr">#<?php echo esc_html($order->get_order_number()); ?></bdi></strong>
                                <span class="ra-home-order-status"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span>
                            </div>
                            <?php if ($items) : ?>
                                <p class="ra-home-products"><?php foreach ($items as $index => $item) : ?>
                                    <?php if ($index) echo '، '; ?><bdi><?php echo esc_html($item->get_name()); ?></bdi>
                                <?php endforeach; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="ra-home-order-action">
                            <?php if ($needs_payment) : ?>
                                <strong><?php echo wp_kses_post(Dashboard::digits($order->get_formatted_order_total())); ?></strong>
                            <?php endif; ?>
                            <a class="ra-button<?php echo $needs_payment ? ' ra-primary' : ''; ?>" href="<?php echo esc_url($action_url); ?>">
                                <?php echo $needs_payment ? 'پرداخت سفارش' : 'مشاهدهٔ سفارش'; ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <?php if ($shortcuts || $show_club) : ?>
        <div class="ra-home-aside">
            <?php if ($shortcuts) : ?>
                <nav class="ra-home-card ra-home-shortcuts" aria-label="دسترسی سریع" data-ra-widget="shortcuts">
                    <?php foreach ($shortcuts as $endpoint => list($icon, $label)) : ?>
                        <a href="<?php echo esc_url(Plugin::url($endpoint)); ?>">
                            <span class="ra-home-shortcut-icon"><?php echo Plugin::icon($icon); ?></span>
                            <span class="ra-home-shortcut-label"><?php echo esc_html($label); ?></span>
                            <?php echo Plugin::icon('arrow-left'); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
            <?php if ($show_club) : ?>
                <section class="ra-home-card ra-wallet" aria-labelledby="ra-wallet-title" data-ra-widget="club">
                    <header class="ra-home-section-heading">
                        <h2 id="ra-wallet-title">اعتبار باشگاه</h2>
                        <a class="ra-home-text-link" href="<?php echo esc_url(Plugin::url('customer-club')); ?>">باشگاه من <?php echo Plugin::icon('arrow-left'); ?></a>
                    </header>
                    <dl class="ra-home-wallet-values">
                        <div>
                            <dt>اعتبار خرید</dt>
                            <dd><bdi><?php echo esc_html(Dashboard::digits(number_format_i18n($club['credit_amount'], wc_get_price_decimals()))); ?></bdi> <?php echo esc_html(html_entity_decode($club['currency'], ENT_QUOTES, 'UTF-8')); ?></dd>
                        </div>
                        <div>
                            <dt><?php echo esc_html($club['unit']); ?></dt>
                            <dd><bdi><?php echo esc_html(Plugin::number($club['balance'])); ?></bdi></dd>
                        </div>
                    </dl>
                </section>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php Extensions::widgets(); ?>
<?php do_action('woocommerce_account_dashboard'); do_action('woocommerce_before_my_account'); do_action('woocommerce_after_my_account'); ?>
