<?php
defined('ABSPATH') || exit;

use Riffside\Account\Dashboard;
use Riffside\Account\Plugin;
use Riffside\Account\Receipt;

$reward = Receipt::reward($order);
$order_id = $order->get_id();
$owns_order = $order->get_customer_id() && (int) $order->get_customer_id() === get_current_user_id();
?>
<div class="woocommerce-order ra-receipt__order">
    <?php do_action('woocommerce_before_thankyou', $order_id); ?>
    <section class="ra-receipt__summary" aria-labelledby="ra-receipt-title">
        <div class="ra-receipt__status">
            <span class="ra-receipt__check"><?php echo Plugin::icon('check'); ?></span>
            <h1 id="ra-receipt-title" class="woocommerce-thankyou-order-received"><?php
                echo esc_html(apply_filters('woocommerce_thankyou_order_received_text', 'خریدت با موفقیت ثبت شد', $order));
            ?></h1>
        </div>

        <?php if ($reward !== '') : ?>
            <div class="ra-receipt__reward"><?php echo $reward; // The Club renderer escapes its card. ?></div>
        <?php endif; ?>

        <dl class="ra-receipt__facts">
            <div>
                <dt>شماره سفارش</dt>
                <dd><bdi><?php echo esc_html(Dashboard::digits($order->get_order_number())); ?></bdi></dd>
            </div>
            <div>
                <dt>مبلغ سفارش</dt>
                <dd><?php echo wp_kses_post(Dashboard::digits($order->get_formatted_order_total())); ?></dd>
            </div>
        </dl>

        <?php if ($owns_order) : ?>
            <a class="ra-receipt__action" href="<?php echo esc_url($order->get_view_order_url()); ?>">
                مشاهده سفارش <?php echo Plugin::icon('arrow-left'); ?>
            </a>
        <?php endif; ?>
    </section>

    <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order_id); ?>
    <?php if (has_action('woocommerce_thankyou')) : ?>
        <details class="ra-receipt__details">
            <summary>ریز سفارش</summary>
            <div class="ra-receipt__details-content">
                <?php Receipt::details($order, $reward !== ''); ?>
            </div>
        </details>
    <?php endif; ?>
</div>
