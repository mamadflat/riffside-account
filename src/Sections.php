<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class Sections {
    public static function current() {
        $endpoint = WC()->query->get_current_endpoint();
        if ($endpoint === '') {
            // Some providers register a WordPress rewrite and an account action directly.
            global $wp;
            foreach ($wp->query_vars as $key => $value) {
                if (has_action('woocommerce_account_' . $key . '_endpoint')) {
                    $endpoint = $key;
                    break;
                }
            }
        }
        if (in_array($endpoint, ['', 'customer-logout', 'lost-password'], true)
            || str_starts_with($endpoint, 'customer-club')) return '';

        if (in_array($endpoint, ['licenses', Appearance::settings()['license_endpoint']], true)) return 'licenses';
        return match ($endpoint) {
            'ra-support', 'tickets' => 'tickets',
            'edit-account', 'edit-address', 'downloads', 'orders', 'view-order' => $endpoint,
            'payment-methods', 'add-payment-method' => 'payment-methods',
            default => 'extension',
        };
    }

    public static function title($section) {
        $title = Plugin::title();
        if ($title !== 'خانه') return $title;
        return match ($section) {
            'licenses' => 'لایسنس‌ها و دانلودها',
            'payment-methods' => 'روش‌های پرداخت',
            'extension' => 'حساب کاربری',
            default => $title,
        };
    }

    public static function intro($section) {
        if ($section === 'tickets') {
            $list = empty($_GET['rp_ticket']) && empty($_GET['ticket']);
            self::heading('ticket', 'تیکت‌های پشتیبانی', 'درخواست تازه ثبت کن یا پاسخ تیکت‌هایت را ببین.',
                $list && !Modules::legacy_portal_active() ? '#ra-new-ticket' : '', 'تیکت جدید');
            return;
        }
        $intro = [
            'edit-account' => ['account', 'اطلاعات حساب', 'نام، ایمیل، شماره تلفن و رمز حسابت را اینجا به‌روز کن.'],
            'edit-address' => ['corporate', 'نشانی‌ها', 'نشانی ارسال و صورتحساب سفارش‌هایت را مدیریت کن.'],
            'downloads' => ['download', 'دانلودهای من', 'فایل‌های قابل دانلود سفارش‌هایت را اینجا پیدا کن.'],
            'payment-methods' => ['plugins', 'روش‌های پرداخت', 'روش‌های پرداخت ذخیره‌شدهٔ این حساب را مدیریت کن.'],
        ][$section] ?? null;
        if ($intro) self::heading(...$intro);
    }

    public static function heading($icon, $title, $description, $action_url = '', $action_text = '') {
        ?>
        <header class="ra-section-heading">
            <span class="ra-section-icon"><?php echo Plugin::icon($icon); ?></span>
            <div>
                <h1><?php echo esc_html($title); ?></h1>
                <p><?php echo esc_html($description); ?></p>
            </div>
            <?php if ($action_url) : ?>
                <a class="ra-button ra-primary" href="<?php echo esc_url($action_url); ?>"><?php echo esc_html($action_text); ?></a>
            <?php endif; ?>
        </header>
        <?php
    }
}
