<?php
defined('ABSPATH') || exit;
use Riffside\Account\Appearance;
use Riffside\Account\Plugin;
$s=Appearance::settings();$option=Appearance::OPTION;
$labels=['background'=>'زمینهٔ صفحه','surface'=>'زمینهٔ کارت','raised'=>'زمینهٔ فرعی','text'=>'متن اصلی','muted'=>'متن فرعی','border'=>'حاشیه','accent'=>'لینک و آیکون','accent_soft'=>'زمینهٔ تأکیدی','button'=>'دکمه','button_hover'=>'دکمه هنگام اشاره','button_text'=>'متن دکمه','success'=>'متن موفقیت','success_soft'=>'زمینهٔ موفقیت','danger'=>'متن خطا','danger_soft'=>'زمینهٔ خطا'];
$license_provider=has_action('woocommerce_account_'.$s['license_endpoint'].'_endpoint');
?>
<div class="wrap ra-admin ra-settings">
 <header class="ra-admin-header"><div><p class="ra-admin-eyebrow">ریف‌ساید · مدیریت حساب مشتریان</p><h1>تنظیمات پنل کاربری</h1><p>امکانات این سایت و ظاهر داشبورد را از همین‌جا مدیریت کن.</p></div><a class="button" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" target="_blank" rel="noopener">مشاهدهٔ پنل ↗</a></header>
 <?php settings_errors(); ?>
 <form action="options.php" method="post" id="ra-settings-form">
 <?php settings_fields('riffside_account'); ?>
 <nav class="ra-settings-tabs" aria-label="بخش‌های تنظیمات">
  <a href="#ra-features" data-ra-settings-tab="features">امکانات پنل</a><a href="#ra-appearance" data-ra-settings-tab="appearance">ظاهر و رنگ‌ها</a><a href="#ra-connections" data-ra-settings-tab="connections">اتصال افزونه‌ها</a>
 </nav>
 <div id="ra-features" data-ra-settings-panel="features">
  <section class="ra-admin-intro"><div><h2>امکانات مورد نیاز این سایت</h2><p>هر بخش را فقط در سایتی که به آن نیاز دارد فعال کن. تغییرات پس از ذخیره اعمال می‌شوند.</p></div><span class="ra-admin-badge">داشبورد مستقل</span></section>
  <div class="ra-feature-grid">
   <?php foreach(['support'=>['ticket','تیکت و پشتیبانی','مشتری از داخل همین پنل تیکت ثبت می‌کند و پاسخ مدیر را می‌بیند.','با خاموش‌کردن، منو، کارت‌ها و ثبت و نمایش تیکت بسته می‌شوند.'],'licenses'=>['plugins','لایسنس‌های مشتری','بخش لایسنس افزونهٔ موجود را به حساب مشتری متصل می‌کند.','مدیریت و صدور لایسنس با همان افزونهٔ لایسنس است.']] as $key=>$item): ?>
   <section class="ra-feature-card" data-ra-feature-card="<?php echo esc_attr($key); ?>">
    <div class="ra-feature-heading"><span class="ra-feature-icon"><?php echo Plugin::icon($item[0]); ?></span><span class="ra-feature-status" data-ra-feature-status><?php echo $s['features'][$key]?'فعال':'غیرفعال'; ?></span></div>
    <h2><?php echo esc_html($item[1]); ?></h2><p><?php echo esc_html($item[2]); ?></p><p class="description"><?php echo esc_html($item[3]); ?></p>
    <input type="hidden" name="<?php echo esc_attr($option); ?>[features][<?php echo esc_attr($key); ?>]" value="0">
    <label class="ra-feature-switch" for="ra-feature-<?php echo esc_attr($key); ?>"><span>فعال‌سازی <?php echo esc_html($item[1]); ?></span><input id="ra-feature-<?php echo esc_attr($key); ?>" type="checkbox" name="<?php echo esc_attr($option); ?>[features][<?php echo esc_attr($key); ?>]" value="1" <?php checked($s['features'][$key]); ?>></label>
   </section>
   <?php endforeach; ?>
  </div>
  <section class="ra-admin-note"><h2>بخش‌های اصلی حساب</h2><p>سفارش‌ها، نشانی‌ها و اطلاعات حساب از ووکامرس می‌آیند. باشگاه مشتریان و تب‌های افزونه‌های فعال هم در همین داشبورد قرار می‌گیرند.</p><p>خاموش‌کردن یک امکان، سوابق قبلی آن را پاک نمی‌کند؛ با فعال‌سازی دوباره، همان سوابق در دسترس قرار می‌گیرند.</p></section>
 </div>
 <div id="ra-appearance" data-ra-settings-panel="appearance">
  <section><h2>قالب نمایش و حالت رنگ</h2><p>فونت پنل از قالب سایت گرفته می‌شود. رنگ‌های روشن و تیره مستقل‌اند.</p>
   <div class="ra-appearance-controls"><div><label for="ra-preset">قالب نمایش</label><select id="ra-preset" name="<?php echo esc_attr($option); ?>[theme]"><?php foreach(Appearance::presets() as $key=>$preset): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($s['theme'],$key); ?>><?php echo esc_html($preset['label']); ?></option><?php endforeach; ?></select><button type="button" class="button" data-ra-apply-preset>اعمال رنگ‌های قالب</button></div>
   <div><label for="ra-mode">حالت نمایش</label><select id="ra-mode" name="<?php echo esc_attr($option); ?>[mode]"><?php foreach(['light'=>'فقط روشن','dark'=>'فقط تیره','both'=>'هر دو؛ با انتخاب مشتری'] as $key=>$label): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($s['mode'],$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></div>
   <div><label for="ra-default">تم پیش‌فرض</label><select id="ra-default" name="<?php echo esc_attr($option); ?>[default_mode]"><option value="light" <?php selected($s['default_mode'],'light'); ?>>روشن</option><option value="dark" <?php selected($s['default_mode'],'dark'); ?>>تیره</option></select></div></div>
   <p class="description">انتخاب نام قالب، رنگ‌های سفارشی را پاک نمی‌کند. برای جایگزینی رنگ‌ها دکمهٔ اعمال و سپس ذخیره را بزن.</p>
  </section>
  <div class="ra-admin-palettes">
   <?php foreach(['light'=>'تم روشن','dark'=>'تم تیره'] as $mode=>$name): ?>
   <section data-ra-palette="<?php echo esc_attr($mode); ?>"><h2><?php echo esc_html($name); ?></h2>
    <div class="ra-palette-preview" data-ra-preview><small>پیش‌نمایش ظاهر</small><h3>حساب کاربری شما</h3><div><strong>آخرین سفارش‌ها</strong><p>جزئیات خرید و وضعیت سفارش را اینجا ببین.</p><span class="ra-preview-button">مشاهدهٔ سفارش</span></div></div>
    <div class="ra-color-grid"><?php foreach($labels as $key=>$label): ?><label><span><?php echo esc_html($label); ?></span><input type="color" data-ra-color="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($option); ?>[palettes][<?php echo esc_attr($mode); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($s['palettes'][$mode][$key]); ?>"></label><?php endforeach; ?></div>
    <label>گردی دکمه (پیکسل)<input type="number" min="0" max="24" data-ra-color="button_radius" name="<?php echo esc_attr($option); ?>[palettes][<?php echo esc_attr($mode); ?>][button_radius]" value="<?php echo (int)$s['palettes'][$mode]['button_radius']; ?>"></label>
   </section><?php endforeach; ?>
  </div>
 </div>
 <div id="ra-connections" data-ra-settings-panel="connections">
  <section><h2>اتصال لایسنس</h2><p>پنل از بخش حساب کاربریِ افزونهٔ لایسنس نصب‌شده استفاده می‌کند.</p><span class="ra-admin-badge"><?php echo $license_provider?'افزونهٔ ارائه‌دهنده شناسایی شد':'ارائه‌دهنده‌ای برای این مسیر شناسایی نشد'; ?></span><label for="ra-license-endpoint">نام مسیر حساب در افزونهٔ لایسنس</label><input id="ra-license-endpoint" name="<?php echo esc_attr($option); ?>[license_endpoint]" value="<?php echo esc_attr($s['license_endpoint']); ?>" dir="ltr" pattern="[a-z][a-z0-9-]{1,49}" maxlength="50"><p class="description">نام مسیر را از تنظیمات افزونهٔ لایسنس بگیر؛ پیش‌فرض <code dir="ltr">licenses</code> است. بدون ارائه‌دهنده، بخش جدیدی ساخته نمی‌شود.</p></section>
  <section><h2>اتصال پشتیبانی</h2><p><?php echo class_exists('Riffside_Portal')?'پرتال ریف‌ساید شناسایی شده؛ گفت‌وگوها از همان پرتال نمایش داده می‌شوند.':'تیکت داخلی پنل آماده است. پس از فعال‌سازی، منوی تیکت‌ها برای مدیر و بخش پشتیبانی برای مشتری ظاهر می‌شود.'; ?></p></section>
  <?php if(class_exists('\Customer_Club\Admin')): ?><section><h2>باشگاه مشتریان</h2><p>موجودی و تراکنش‌ها از افزونهٔ باشگاه خوانده می‌شوند. ظاهر باشگاه تنظیمات مستقل دارد.</p><a class="button" href="<?php echo esc_url(\Customer_Club\Admin::dashboard_url('settings',['section'=>'appearance'])); ?>">تنظیمات ظاهر باشگاه مشتریان</a></section><?php endif; ?>
 </div>
 <div class="ra-settings-save"><span data-ra-save-status role="status">تنظیمات ذخیره‌شدهٔ این سایت</span><button type="submit" id="submit" class="button button-primary">ذخیرهٔ تنظیمات پنل</button></div>
 </form><p class="ra-version">پنل ریف‌ساید <bdi dir="ltr"><?php echo esc_html(RIFFSIDE_ACCOUNT_VERSION); ?></bdi></p>
</div>
