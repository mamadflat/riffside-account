<?php
defined('ABSPATH') || exit;
?>
<div class="wrap ra-admin ra-settings">
    <header class="ra-admin-header">
        <div><h1>افزونه‌ها</h1></div>
    </header>

    <section class="ra-feature-card">
        <h2>باشگاه مشتریان ووکامرس</h2>
        <p>امتیاز، اعتبار و فعالیت‌های باشگاه در حساب مشتری نمایش داده می‌شوند.</p>
        <p><strong><?php echo esc_html($active ? 'فعال' : ($installed ? 'نصب‌شده، غیرفعال' : 'نصب نشده')); ?></strong></p>
        <?php if (!$installed): ?>
            <a class="button button-primary" href="<?php echo esc_url($product_url); ?>">خرید از ریف‌ساید</a>
            <?php if (current_user_can('install_plugins')): ?>
                <a class="button" href="<?php echo esc_url(admin_url('plugin-install.php?tab=upload')); ?>">بارگذاری فایل افزونه</a>
            <?php endif; ?>
        <?php elseif ($activation_url): ?>
            <a class="button button-primary" href="<?php echo esc_url($activation_url); ?>">فعال‌سازی باشگاه</a>
        <?php elseif ($active && is_callable([\Customer_Club\Admin::class, 'dashboard_url'])): ?>
            <a class="button" href="<?php echo esc_url(\Customer_Club\Admin::dashboard_url('settings')); ?>">تنظیمات باشگاه</a>
        <?php endif; ?>
    </section>

    <section class="ra-feature-card">
        <h2>مدیریت لایسنس ریف‌ساید</h2>
        <p>برای محصولات خودت لایسنس صادر کن و دانلود و دریافت آپدیت را برای خریدار مدیریت کن.</p>
        <a class="button" href="https://riffside.ir/plugins/riffside-license-manager/">مشاهدهٔ افزونه در ریف‌ساید</a>
    </section>
</div>
