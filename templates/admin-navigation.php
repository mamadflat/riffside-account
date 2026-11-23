<?php
defined('ABSPATH') || exit;
use Riffside\Account\CustomTabs;
use Riffside\Account\Dashboard;
use Riffside\Account\Navigation;
use Riffside\Account\Plugin;

$initial_mode = $appearance['mode'] === 'both' ? $appearance['default_mode'] : $appearance['mode'];
$shortcuts = Dashboard::shortcuts($menu);
?>
<div class="wrap ra-admin ra-settings ra-navigation-settings" dir="rtl" data-ra-navigation-admin>
    <header class="ra-admin-header">
        <div><h1>منو و داشبورد</h1><p>ترتیب و نمایش بخش‌های حساب مشتری را تنظیم کن.</p></div>
        <?php if (current_user_can('manage_options')) : ?>
            <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type=' . CustomTabs::TYPE)); ?>">افزودن تب سفارشی</a>
        <?php endif; ?>
    </header>
    <?php settings_errors(); ?>
    <div class="ra-navigation-layout">
        <form action="options.php" method="post">
            <?php settings_fields(Navigation::GROUP); ?>
            <section aria-labelledby="ra-menu-settings-title">
                <h2 id="ra-menu-settings-title">ترتیب منو</h2>
                <p class="description">از دکمه‌های بالا و پایین استفاده کن. غیرفعال‌کردن یک بخش، دسترسی آن را از حساب مشتری برمی‌دارد.</p>
                <ol class="ra-menu-edit-list" data-ra-menu-editor>
                    <?php foreach ($menu as $key => $label) :
                        $label = wp_strip_all_tags($label);
                        $required = in_array($key, Navigation::REQUIRED, true);
                        $fixed = $key === 'customer-logout';
                        $visible = $required || !in_array($key, $settings['hidden'], true);
                    ?>
                        <li class="ra-menu-edit-row" data-ra-menu-key="<?php echo esc_attr($key); ?>" <?php if ($fixed) echo 'data-ra-fixed'; ?>>
                            <input type="hidden" name="<?php echo esc_attr(Navigation::OPTION); ?>[order][]" value="<?php echo esc_attr($key); ?>">
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(Navigation::OPTION); ?>[enabled][<?php echo esc_attr($key); ?>]" value="1" <?php checked($visible); disabled($required); ?>>
                                <?php echo Plugin::icon(Navigation::icon($key)); ?>
                                <span><?php echo esc_html($label); ?></span>
                            </label>
                            <?php if (!$fixed) : ?><div class="ra-menu-row-controls">
                                <button class="button" type="button" data-ra-menu-move="up" aria-label="<?php echo esc_attr('بالا بردن ' . $label); ?>">↑</button>
                                <button class="button" type="button" data-ra-menu-move="down" aria-label="<?php echo esc_attr('پایین بردن ' . $label); ?>">↓</button>
                            </div><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <p class="description">خانه و خروج همیشه در دسترس‌اند.</p>
            </section>
            <div class="ra-settings-save"><span role="status" data-ra-menu-save>تنظیمات ذخیره‌شده</span><button type="submit" class="button button-primary">ذخیرهٔ منو</button></div>
        </form>
        <section aria-labelledby="ra-menu-preview-title">
            <h2 id="ra-menu-preview-title">پیش‌نمایش پنل</h2>
            <p class="description">این نمونه با تغییر ترتیب یا نمایش منو به‌روز می‌شود.</p>
            <div class="ra-preview-tools">
                <label for="ra-preview-theme">تم پیش‌نمایش</label>
                <select id="ra-preview-theme" data-ra-preview-theme>
                    <option value="light" <?php selected($initial_mode, 'light'); ?>>روشن</option>
                    <option value="dark" <?php selected($initial_mode, 'dark'); ?>>تیره</option>
                </select>
            </div>
            <div class="riffside-account ra-navigation-preview" data-ra-theme="<?php echo esc_attr($initial_mode); ?>">
                <ul class="ra-menu-preview-list" data-ra-menu-preview>
                    <?php foreach ($menu as $key => $label) : ?>
                        <li data-ra-preview-key="<?php echo esc_attr($key); ?>" <?php if (in_array($key, $settings['hidden'], true)) echo 'hidden'; ?>><?php echo Plugin::icon(Navigation::icon($key)); ?><span><?php echo esc_html(wp_strip_all_tags($label)); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <div class="ra-home ra-home-preview">
                    <div class="ra-home-card ra-home-welcome">
                        <div class="ra-home-profile"><span class="ra-home-profile-icon"><?php echo Plugin::icon('account'); ?></span><div><h3>خوش اومدی، کاربر نمونه</h3><p>شماره تلفن <bdi dir="ltr">09120000000</bdi></p></div></div>
                        <span class="ra-preview-action" data-ra-if-menu="edit-account">ویرایش اطلاعات حساب</span>
                    </div>
                    <div class="ra-home-shortcuts" data-ra-preview-shortcuts>
                        <?php foreach ($shortcuts as $key => [$icon, $label]) : ?>
                            <span class="ra-home-shortcut" data-ra-if-menu="<?php echo esc_attr($key); ?>"><span class="ra-home-shortcut-icon"><?php echo Plugin::icon($icon); ?></span><span><?php echo esc_html($label); ?></span></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="ra-home-metrics" data-ra-if-menu="orders">
                        <?php foreach (['جاری' => '۲', 'تکمیل‌شده' => '۴', 'سایر سفارش‌ها' => '۱'] as $label => $count) : ?>
                            <span class="ra-home-metric"><span><?php echo esc_html($label); ?></span><strong><?php echo esc_html($count); ?></strong></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="ra-home-card ra-preview-order" data-ra-if-menu="orders">
                        <strong>وضعیت سفارش‌های نمونه</strong>
                        <div><span class="ra-home-order-status" data-ra-order-tone="success">تکمیل‌شده</span><span class="ra-home-order-status" data-ra-order-tone="danger">لغوشده</span></div>
                    </div>
                </div>
            </div>
            <details class="ra-mobile-preview" data-ra-mobile-preview>
                <summary>نمای موبایل حساب من</summary>
                <p class="description">این نما حساب فعلی‌ات را با تنظیمات ذخیره‌شده نشان می‌دهد.</p>
                <iframe title="حساب کاربری در عرض موبایل" data-src="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"></iframe>
            </details>
        </section>
    </div>
</div>
