<?php
namespace Riffside\Account;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

defined('ABSPATH') || exit;

final class PortalTickets {
    const TYPES = ['rp_ticket', 'rp_message'];
    const RECIPIENT = 'riffside_account_ticket_notification_email';

    public static function selected() {
        return get_option('riffside_portal_version', false) !== false;
    }

    public static function remember_recipient() {
        if (defined('Riffside_Portal::ADMIN_EMAIL') && is_email(\Riffside_Portal::ADMIN_EMAIL)) {
            add_option(self::RECIPIENT, \Riffside_Portal::ADMIN_EMAIL, '', false);
        }
    }

    public static function boot() {
        add_action('init', [__CLASS__, 'types']);
        add_action('init', [__CLASS__, 'refresh_rewrites'], 20);
        add_shortcode('riffside_account_tickets', [__CLASS__, 'view']);
        add_shortcode('riffside_portal', [__CLASS__, 'view']);
        add_action('woocommerce_account_tickets_endpoint', static function () { echo self::view(); });
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('add_meta_boxes_rp_ticket', [__CLASS__, 'meta_boxes']);
        add_action('save_post_rp_ticket', [__CLASS__, 'admin_save'], 20, 2);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
        add_action('admin_post_riffside_portal', [__CLASS__, 'handle']);
        add_action('admin_post_nopriv_riffside_portal', [__CLASS__, 'reject_guest']);
        add_action('admin_post_riffside_portal_file', [__CLASS__, 'download']);
        add_action('admin_post_nopriv_riffside_portal_file', [__CLASS__, 'reject_guest']);
        add_filter('pre_option_woocommerce_enable_myaccount_registration', static function () { return 'yes'; });
        add_filter('pre_option_woocommerce_registration_generate_username', static function () { return 'yes'; });
        add_filter('pre_option_woocommerce_registration_generate_password', static function () { return 'no'; });
    }

    public static function refresh_rewrites() {
        if (add_option('riffside_account_ticket_rewrites', 1, '', false)) flush_rewrite_rules(false);
    }

    public static function types() {
        $labels = ['rp_ticket' => 'تیکت‌ها', 'rp_message' => 'پیام‌ها'];
        foreach ($labels as $type => $label) {
            register_post_type($type, [
                'label' => $label, 'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true,
                'show_ui' => $type !== 'rp_message', 'show_in_menu' => 'riffside-account', 'show_in_rest' => false,
                'rewrite' => false, 'query_var' => false, 'supports' => ['title', 'editor'], 'can_export' => false,
                'map_meta_cap' => false,
                'capabilities' => array_fill_keys(['edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts', 'create_posts'], 'manage_options'),
            ]);
        }
    }

    public static function assets() {
        wp_register_style('riffside-account-tickets', plugins_url('assets/portal-tickets.css', RIFFSIDE_ACCOUNT_FILE), [], RIFFSIDE_ACCOUNT_VERSION);
        wp_register_script('riffside-account-tickets', plugins_url('assets/portal-tickets.js', RIFFSIDE_ACCOUNT_FILE), [], RIFFSIDE_ACCOUNT_VERSION, true);
    }

    private static function enqueue() {
        self::assets();
        wp_enqueue_style('riffside-account-tickets');
        wp_enqueue_script('riffside-account-tickets');
        if (is_user_logged_in()) {
            if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
            if (!headers_sent()) nocache_headers();
        }
    }

    private static function url() { return Plugin::url('tickets'); }

    private static function e($value) { return esc_html((string) $value); }
    private static function meta($id, $key, $default = '') { $v = get_post_meta($id, '_rp_' . $key, true); return $v === '' ? $default : $v; }
    private static function put($id, $key, $value) { return update_post_meta($id, '_rp_' . $key, $value); }
    private static function admin() { return current_user_can('manage_options'); }

    public static function owner($id) {
        $post = get_post($id);
        if (!$post || !in_array($post->post_type, self::TYPES, true) || $post->post_status === 'trash') return 0;
        if ($post->post_type === 'rp_message') {
            return get_post_type($post->post_parent) === 'rp_ticket' ? self::owner($post->post_parent) : 0;
        }
        return (int) self::meta($id, 'owner');
    }

    public static function allowed($id) {
        $post = get_post($id);
        if (!is_user_logged_in() || !$post || !in_array($post->post_type, self::TYPES, true) || $post->post_status === 'trash') return false;
        return self::admin() || (self::owner($id) > 0 && self::owner($id) === get_current_user_id());
    }

    private static function owned($type, $limit = 100) {
        $query = ['post_type' => $type, 'post_status' => ['private', 'publish'], 'numberposts' => $limit, 'orderby' => 'date', 'order' => 'DESC'];
        $query['meta_query'] = [['key' => '_rp_owner', 'value' => get_current_user_id(), 'compare' => '=', 'type' => 'NUMERIC']];
        return array_values(array_filter(get_posts($query), static function ($p) { return self::allowed($p->ID); }));
    }

    private static function form_start($action, $return, $parent = 0, $multipart = false) {
        $html = '<form class="rp-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '"' . ($multipart ? ' enctype="multipart/form-data"' : '') . '>';
        $html .= '<input type="hidden" name="action" value="riffside_portal"><input type="hidden" name="rp_action" value="' . esc_attr($action) . '"><input type="hidden" name="rp_return" value="' . esc_attr($return) . '"><input type="hidden" name="rp_parent" value="' . absint($parent) . '"><input type="hidden" name="rp_token" value="' . esc_attr(wp_generate_uuid4()) . '">';
        return $html . wp_nonce_field('riffside_portal_' . $action, '_rp_nonce', true, false);
    }

    private static function notice() {
        $key = 'rp_notice_' . get_current_user_id();
        $notice = get_transient($key);
        if (!$notice) return '';
        delete_transient($key);
        return '<p class="rp-notice" role="status">' . self::e($notice) . '</p>';
    }

    private static function guest() {
        return '<div class="rp-empty"><h2>اول وارد حسابت شو.</h2><p>اطلاعات و گفت‌وگوهای تو فقط در حساب خودت نمایش داده می‌شوند.</p><a class="button" href="' . esc_url(wc_get_page_permalink('myaccount')) . '">ورود / ثبت‌نام</a></div>';
    }

    private static function blank($title, $text) { return '<div class="rp-empty"><h3>' . self::e($title) . '</h3><p>' . self::e($text) . '</p></div>'; }

    public static function view($attrs = []) {
        if (!Appearance::enabled('support') || (is_array($attrs) && isset($attrs['view']) && $attrs['view'] !== 'tickets')) return '';
        self::enqueue();
        return '<div class="rp-root" dir="rtl" lang="fa">' . self::notice()
            . (is_user_logged_in() ? self::tickets_view() : self::guest()) . '</div>';
    }

    private static function tickets_view() {
        $selected = absint($_GET['rp_ticket'] ?? 0);
        if ($selected) {
            $ticket = get_post($selected);
            if (!$ticket || $ticket->post_type !== 'rp_ticket' || !self::allowed($selected)) return self::blank('تیکت در دسترس نیست.', 'از فهرست تیکت‌های حساب خودت وارد شو.');
            $return = add_query_arg('rp_ticket', $selected, self::url());
            return '<a href="' . esc_url(self::url()) . '">همه تیکت‌ها</a><article class="rp-card"><h2>' . self::e($ticket->post_title) . '</h2><p>' . (self::meta($selected, 'status') === 'closed' ? 'بسته‌شده' : 'باز') . '</p><div class="rp-prose">' . nl2br(self::e($ticket->post_content)) . '</div>' . self::thread($selected) . self::file_list($selected) . self::reply_form($selected, $return) . self::upload_form($selected, $return) . '</article>';
        }
        $html = '';
        $items = self::owned('rp_ticket');
        if (!$items) $html .= self::blank('هنوز تیکتی نداری.', 'برای پشتیبانی افزونه یا ادامه گفت‌وگو، درخواستت را در فرم زیر بنویس.');
        foreach ($items as $item) $html .= '<article class="rp-card"><h3><a href="' . esc_url(add_query_arg('rp_ticket', $item->ID, self::url())) . '">' . self::e($item->post_title) . '</a></h3><p>' . (self::meta($item->ID, 'status') === 'closed' ? 'بسته‌شده' : 'باز') . '</p></article>';
        return $html . '<div class="rp-card"><h2>تیکت تازه</h2>' . self::form_start('ticket', self::url()) . '<label>موضوع<input name="subject" maxlength="180" required></label><label>نام محصول یا پروژه<input name="context" maxlength="180"></label><label>چه کمکی نیاز داری؟<textarea name="message" rows="6" maxlength="16000" required></textarea></label><p>شرح مشکل و مراحل تکرار آن را بنویس. رمز عبور را در متن تیکت وارد نکن.</p><button class="button" type="submit">ثبت تیکت</button></form></div>';
    }

    private static function thread($parent) {
        if (!self::allowed($parent)) return '';
        $messages = get_posts(['post_type' => 'rp_message', 'post_status' => 'private', 'post_parent' => $parent, 'numberposts' => 200, 'orderby' => 'date', 'order' => 'ASC']);
        $html = '<div class="rp-thread">';
        foreach ($messages as $message) {
            $staff = self::meta($message->ID, 'staff') === '1';
            $html .= '<div class="rp-message' . ($staff ? ' rp-message-staff' : '') . '"><strong>' . ($staff ? 'ریف‌ساید' : 'شما') . '</strong><time datetime="' . esc_attr(get_post_time('c', true, $message)) . '">' . self::e(get_the_date('Y/m/d H:i', $message)) . '</time><div>' . nl2br(self::e($message->post_content)) . '</div></div>';
        }
        return $html . '</div>';
    }

    private static function reply_form($parent, $return) {
        if (!self::allowed($parent)) return '';
        return self::form_start('reply', $return, $parent) . '<label>پیام تازه<textarea name="message" maxlength="16000" rows="4" required></textarea></label><button class="button" type="submit">ارسال پیام</button></form>';
    }

    private static function file_list($parent) {
        if (get_post_type($parent) !== 'rp_ticket' || !self::allowed($parent)) return '';
        global $wpdb;
        $table = $wpdb->prefix . 'riffside_portal_files';
        $rows = $wpdb->get_results($wpdb->prepare("SELECT id,original_name,byte_size,uploaded_by,created_at FROM $table WHERE parent_id=%d ORDER BY id DESC LIMIT 100", $parent));
        if (!$rows) return '<p>هنوز فایلی برای این مورد ثبت نشده است.</p>';
        $html = '<ul class="rp-files">';
        foreach ($rows as $row) {
            $url = wp_nonce_url(admin_url('admin-post.php?action=riffside_portal_file&id=' . (int) $row->id), 'riffside_portal_file_' . $row->id);
            $sender = (int) $row->uploaded_by === self::owner($parent) ? 'مشتری' : 'ریف‌ساید';
            $html .= '<li><a href="' . esc_url($url) . '">' . self::e($row->original_name) . '</a><small>' . self::e(size_format($row->byte_size)) . ' · ' . self::e($sender) . ' · ' . self::e(wp_date('Y/m/d H:i', strtotime($row->created_at . ' UTC'))) . '</small></li>';
        }
        return $html . '</ul>';
    }

    private static function upload_form($parent, $return) {
        if (get_post_type($parent) !== 'rp_ticket' || !self::allowed($parent)) return '';
        return self::form_start('upload', $return, $parent, true) . '<label>افزودن فایل<input type="file" name="rp_file" accept=".jpg,.jpeg,.png,.webp,.pdf,.txt,.zip" required></label><p>تصویر JPG، PNG یا WebP؛ PDF، متن یا ZIP. حداکثر ۵ مگابایت برای هر فایل.</p><button class="button" type="submit">ارسال فایل</button></form>';
    }

    public static function reject_guest() { wp_die('برای ادامه، وارد حساب کاربری خودت شو.', 'ورود لازم است', ['response' => 403]); }

    private static function text($key, $max = 20000) {
        $raw = isset($_POST[$key]) && is_string($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
        if (strlen($raw) > $max * 4) throw new RuntimeException('متن واردشده بیش از اندازه طولانی است.');
        return sanitize_textarea_field($raw);
    }

    private static function parent($id, $types) {
        $post = get_post($id);
        if (!$post || !in_array($post->post_type, $types, true) || !self::allowed($id)) throw new RuntimeException('این مورد در دسترس حساب شما نیست.');
        return $post;
    }

    private static function create($type, $title, $content, $owner, $parent = 0) {
        $id = wp_insert_post(['post_type' => $type, 'post_status' => 'private', 'post_title' => $title, 'post_content' => $content, 'post_author' => get_current_user_id(), 'post_parent' => $parent], true);
        if (is_wp_error($id)) throw new RuntimeException('ثبت اطلاعات انجام نشد. دوباره تلاش کن.');
        self::put($id, 'owner', $owner);
        return (int) $id;
    }

    private static function email_owner($id, $subject, $message, $url) {
        $user = get_userdata(self::owner($id));
        if ($user && is_email($user->user_email)) {
            try { wp_mail($user->user_email, $subject . ' | ریف‌ساید', $message . "\n\n" . esc_url_raw($url)); }
            catch (Throwable $e) { error_log('Riffside Portal: customer notification could not be handed to mail transport.'); }
        }
    }

    private static function notify_admin($id, $subject) {
        try { wp_mail(get_option(self::RECIPIENT, get_option('admin_email')), $subject . ' | ریف‌ساید', 'برای مشاهده و پاسخ، وارد مدیریت وردپرس شوید.' . "\n\n" . admin_url('post.php?post=' . absint($id) . '&action=edit')); }
        catch (Throwable $e) { error_log('Riffside Portal: administrator notification could not be handed to mail transport.'); }
    }

    /** Add working minutes in Tehran; boundary itself is the deadline, so response must precede it. */
    public static function due_date($timestamp, $minutes = 180, $holidays = []) {
        $zone = new DateTimeZone('Asia/Tehran');
        $time = (new DateTimeImmutable('@' . (int) $timestamp))->setTimezone($zone);
        $remaining = max(1, (int) $minutes) * 60;
        for ($guard = 0; $guard < 3700; $guard++) {
            $date = $time->format('Y-m-d');
            $open = $time->setTime(9, 0); $close = $time->setTime(21, 0);
            if ($time->format('N') === '5' || in_array($date, $holidays, true) || $time >= $close) { $time = $time->modify('+1 day')->setTime(9, 0); continue; }
            if ($time < $open) $time = $open;
            $available = $close->getTimestamp() - $time->getTimestamp();
            if ($remaining <= $available) return $time->getTimestamp() + $remaining;
            $remaining -= $available; $time = $time->modify('+1 day')->setTime(9, 0);
        }
        throw new RuntimeException('تقویم پشتیبانی قابل محاسبه نیست.');
    }

    public static function handle() {
        if (!is_user_logged_in()) self::reject_guest();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') wp_die('روش درخواست معتبر نیست.', '', ['response' => 405]);
        $action = sanitize_key($_POST['rp_action'] ?? '');
        if (!in_array($action, ['ticket', 'reply', 'upload', 'settings'], true)) wp_die('درخواست معتبر نیست.', '', ['response' => 400]);
        check_admin_referer('riffside_portal_' . $action, '_rp_nonce');
        $default = self::url();
        $return = wp_validate_redirect(esc_url_raw(wp_unslash($_POST['rp_return'] ?? '')), $default);
        $lock = 0;
        try {
            if ($action === 'settings') { self::save_settings(); wp_safe_redirect(admin_url('admin.php?page=riffside-account-ticket-calendar')); exit; }
            $user = wp_get_current_user();
            if (!is_email($user->user_email)) throw new RuntimeException('ایمیل معتبری در تنظیمات حساب ثبت کن.');
            $token = self::text('rp_token', 100);
            if (!preg_match('/^[a-zA-Z0-9-]{20,80}$/', $token)) throw new RuntimeException('شناسه فرم معتبر نیست. صفحه را تازه کن.');
            global $wpdb; $table = $wpdb->prefix . 'riffside_portal_actions';
            $hash = hash('sha256', $action . ':' . $token);
            $inserted = $wpdb->query($wpdb->prepare("INSERT IGNORE INTO $table (user_id,token_hash,created_at) VALUES (%d,%s,%s)", $user->ID, $hash, gmdate('Y-m-d H:i:s')));
            if ($inserted === false) throw new RuntimeException('ثبت اطلاعات موقتاً در دسترس نیست. دوباره تلاش کن.');
            if (!$inserted) throw new RuntimeException('این فرم قبلاً ارسال شده یا در حال ثبت است. فهرست را بررسی کن.');
            $lock = (int) $wpdb->insert_id;
            $recent = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE user_id=%d AND created_at>%s", $user->ID, gmdate('Y-m-d H:i:s', time() - HOUR_IN_SECONDS)));
            if (!self::admin() && $recent > 40) throw new RuntimeException('تعداد درخواست‌ها زیاد شده است. کمی بعد دوباره تلاش کن.');
            $result = 0;
            if ($action === 'ticket') {
                $title = self::text('subject', 180); $content = self::text('message', 16000);
                if (!$title || !$content) throw new RuntimeException('موضوع و متن تیکت را تکمیل کن.');
                $context = self::text('context', 180);
                $due = get_option('riffside_portal_calendar_configured') ? self::due_date(time(), 180, (array) get_option('riffside_portal_holidays', [])) : 0;
                $id = self::create('rp_ticket', $title, $content, $user->ID);
                foreach (['status' => 'open', 'context' => $context, 'admin_unread' => '1', 'due_at' => $due] as $key => $value) self::put($id, $key, $value);
                self::notify_admin($id, 'تیکت تازه');
                $result = $id; $return = add_query_arg('rp_ticket', $id, self::url()); $message = 'تیکت ثبت شد. پاسخ را در همین گفت‌وگو پیگیری کن.';
            } elseif ($action === 'reply') {
                $parent = self::parent(absint($_POST['rp_parent'] ?? 0), ['rp_ticket']);
                $content = self::text('message', 16000);
                if (!$content) throw new RuntimeException('متن پیام را بنویس.');
                $result = self::add_reply($parent->ID, $content, self::admin()); $message = 'پیامت ثبت شد.';
            } elseif ($action === 'upload') {
                $parent = self::parent(absint($_POST['rp_parent'] ?? 0), ['rp_ticket']);
                self::upload($parent->ID); $result = $parent->ID; $message = 'فایل با موفقیت ثبت شد.';
            }
            $wpdb->update($table, ['result_id' => $result], ['id' => $lock], ['%d'], ['%d']);
            set_transient('rp_notice_' . get_current_user_id(), $message, 120);
        } catch (Throwable $e) {
            if ($lock) { global $wpdb; $wpdb->delete($wpdb->prefix . 'riffside_portal_actions', ['id' => $lock], ['%d']); }
            set_transient('rp_notice_' . get_current_user_id(), $e instanceof RuntimeException ? $e->getMessage() : 'ثبت انجام نشد. دوباره تلاش کن.', 120);
        }
        wp_safe_redirect($return, 303); exit;
    }

    private static function add_reply($parent, $content, $staff) {
        $id = self::create('rp_message', 'پیام', $content, self::owner($parent), $parent);
        self::put($id, 'staff', $staff ? '1' : '0');
        if ($staff) {
            self::put($parent, 'admin_unread', '0');
            if (!self::meta($parent, 'first_response_at')) self::put($parent, 'first_response_at', time());
            $url = add_query_arg('rp_ticket', $parent, self::url());
            self::email_owner($parent, 'پاسخ تازه از ریف‌ساید', 'پیام تازه‌ای در حساب کاربری‌ات ثبت شده است.', $url);
        } else {
            self::put($parent, 'admin_unread', '1');
            self::put($parent, 'status', 'open');
            self::notify_admin($parent, 'پیام تازه مشتری');
        }
        return $id;
    }

    private static function file_key() {
        $secret = defined('RIFFSIDE_PORTAL_FILE_KEY') ? (string) RIFFSIDE_PORTAL_FILE_KEY : (string) get_option('riffside_portal_file_key', '');
        $decoded = base64_decode($secret, true);
        if ($decoded !== false && strlen($decoded) === 32) return $decoded;
        if (defined('RIFFSIDE_PORTAL_FILE_KEY') && strlen($secret) >= 32) return hash('sha256', $secret, true);
        throw new RuntimeException('ذخیره امن فایل هنوز آماده نیست. با پشتیبانی هماهنگ کن.');
    }

    private static function upload($parent) {
        if (!function_exists('openssl_encrypt')) throw new RuntimeException('ارسال فایل موقتاً در دسترس نیست.');
        $file = $_FILES['rp_file'] ?? null;
        if (!is_array($file) || is_array($file['name'] ?? null) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) throw new RuntimeException('فایل به‌درستی دریافت نشد.');
        $size = filesize($file['tmp_name']);
        if (!$size || $size > 5 * MB_IN_BYTES) throw new RuntimeException('حجم فایل باید کمتر از ۵ مگابایت باشد.');
        $name = sanitize_file_name($file['name']); $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allow = ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'], 'pdf' => ['application/pdf'], 'txt' => ['text/plain'], 'zip' => ['application/zip', 'application/x-zip', 'application/x-zip-compressed']];
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allow[$ext]) || !in_array($mime, $allow[$ext], true)) throw new RuntimeException('نوع فایل مجاز نیست. تصویر، PDF، متن یا ZIP بفرست.');
        global $wpdb; $table = $wpdb->prefix . 'riffside_portal_files';
        $daily = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE uploaded_by=%d AND created_at>%s", get_current_user_id(), gmdate('Y-m-d H:i:s', time() - DAY_IN_SECONDS)));
        if (!self::admin() && $daily >= 20) throw new RuntimeException('سقف ارسال فایل امروز تکمیل شده است. برای هماهنگی تیکت بزن.');
        $plain = file_get_contents($file['tmp_name']); $iv = random_bytes(12); $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::file_key(), OPENSSL_RAW_DATA, $iv, $tag, (string) $parent);
        unset($plain);
        if ($cipher === false) throw new RuntimeException('ذخیره فایل انجام نشد.');
        $saved = $wpdb->insert($table, ['parent_id' => $parent, 'uploaded_by' => get_current_user_id(), 'storage_key' => bin2hex(random_bytes(24)) . '.enc', 'original_name' => $name, 'mime' => $mime, 'byte_size' => $size, 'iv' => $iv, 'tag' => $tag, 'payload' => $cipher, 'created_at' => gmdate('Y-m-d H:i:s')]);
        if (!$saved) throw new RuntimeException('ذخیره فایل انجام نشد. دوباره تلاش کن.');
    }

    public static function download() {
        if (!is_user_logged_in()) self::reject_guest();
        $id = absint($_GET['id'] ?? 0);
        check_admin_referer('riffside_portal_file_' . $id);
        global $wpdb; $table = $wpdb->prefix . 'riffside_portal_files';
        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id));
        if (!$file || get_post_type($file->parent_id) !== 'rp_ticket' || !self::allowed($file->parent_id)) wp_die('فایل در دسترس نیست.', '', ['response' => 404]);
        try { $plain = openssl_decrypt($file->payload, 'aes-256-gcm', self::file_key(), OPENSSL_RAW_DATA, $file->iv, $file->tag, (string) $file->parent_id); }
        catch (Throwable $e) { $plain = false; }
        if ($plain === false) wp_die('فایل قابل دریافت نیست. با پشتیبانی تماس بگیر.', '', ['response' => 500]);
        nocache_headers(); header('X-Content-Type-Options: nosniff'); header('X-Robots-Tag: noindex, nofollow');
        header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="download.' . sanitize_key(pathinfo($file->original_name, PATHINFO_EXTENSION)) . '"; filename*=UTF-8\'\'' . rawurlencode($file->original_name));
        header('Content-Length: ' . strlen($plain));
        echo $plain; exit;
    }

    public static function admin_menu() {
        add_submenu_page('riffside-account', 'تقویم پشتیبانی', 'تقویم پشتیبانی', 'manage_options', 'riffside-account-ticket-calendar', [__CLASS__, 'settings_view']);
    }

    public static function meta_boxes() {
        add_meta_box('rp-management', 'مدیریت تیکت و حساب مشتری', [__CLASS__, 'box'], 'rp_ticket', 'normal', 'high');
    }

    public static function box($post) {
        if (!self::admin()) return;
        wp_nonce_field('riffside_portal_admin_' . $post->ID, '_rp_admin_nonce');
        $owner = self::owner($post->ID);
        echo '<p><label>صاحب حساب: ';
        wp_dropdown_users(['name' => 'rp_owner', 'selected' => $owner, 'show_option_none' => 'انتخاب حساب', 'option_none_value' => 0, 'show' => 'display_name', 'include_selected' => true]);
        echo '</label></p>';
            echo '<p><label>وضعیت <select name="rp_status"><option value="open">باز</option><option value="closed"' . selected(self::meta($post->ID, 'status'), 'closed', false) . '>بسته‌شده</option></select></label></p><p>محصول یا پروژه: ' . self::e(self::meta($post->ID, 'context')) . '</p>';
            $due = (int) self::meta($post->ID, 'due_at');
            if ($due) echo '<p>مهلت پاسخ اولیه: ' . self::e(wp_date('Y/m/d H:i', $due, new DateTimeZone('Asia/Tehran'))) . ' (تهران)</p>';
            else echo '<p>تقویم تعطیلات برای این تیکت تنظیم نشده؛ مهلت خودکار محاسبه نشده است.</p>';
            if (self::meta($post->ID, 'first_response_at')) echo '<p>پاسخ اولیه ثبت شده است.</p>';
        echo self::thread($post->ID) . '<p><label>پاسخ تازه به مشتری<textarea name="rp_admin_reply" rows="5" class="widefat" maxlength="16000"></textarea></label></p>';
        if ($owner) echo self::file_list($post->ID) . '<p>برای افزودن فایل به این مورد: <a href="' . esc_url(add_query_arg(['page' => 'riffside-account-ticket-calendar', 'rp_upload_parent' => $post->ID], admin_url('admin.php'))) . '">صفحه بارگذاری امن</a></p>';
    }

    public static function admin_save($id, $post) {
        if ($post->post_type !== 'rp_ticket' || !self::admin() || wp_is_post_revision($id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) return;
        if (empty($_POST['_rp_admin_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_rp_admin_nonce'])), 'riffside_portal_admin_' . $id)) return;
        $owner = absint($_POST['rp_owner'] ?? 0);
        if (!$owner || !get_userdata($owner)) { self::admin_error('صاحب حساب معتبر انتخاب کنید؛ تنظیمات ذخیره نشد.'); return; }
        self::put($id, 'owner', $owner);
        self::put($id, 'status', ($_POST['rp_status'] ?? '') === 'closed' ? 'closed' : 'open');
        $reply = sanitize_textarea_field(wp_unslash($_POST['rp_admin_reply'] ?? ''));
        if ($reply !== '' && strlen($reply) <= 64000) self::add_reply($id, $reply, true);
    }

    private static function admin_error($text) { set_transient('rp_admin_notice_' . get_current_user_id(), $text, 120); }
    public static function admin_notice() {
        if (!self::admin()) return;
        $key = 'rp_admin_notice_' . get_current_user_id(); $text = get_transient($key);
        if ($text) { delete_transient($key); echo '<div class="notice notice-warning"><p>' . self::e($text) . '</p></div>'; }
    }

    public static function settings_view() {
        if (!self::admin()) return;
        self::enqueue();
        echo '<div class="wrap rp-root"><h1>تقویم پشتیبانی</h1>' . self::notice() . self::form_start('settings', admin_url('admin.php?page=riffside-account-ticket-calendar')) . '<p>شنبه تا پنجشنبه، ساعت ۹ تا ۲۱ تهران. جمعه‌ها شمرده نمی‌شوند. تاریخ تعطیلات را به میلادی، هر تاریخ در یک خط وارد کنید.</p><label>تعطیلات<textarea name="holidays" rows="10" dir="ltr">' . esc_textarea(implode("\n", (array) get_option('riffside_portal_holidays', []))) . '</textarea></label><button type="submit" class="button button-primary">ذخیره تقویم</button></form>';
        $parent = absint($_GET['rp_upload_parent'] ?? 0);
        if ($parent && self::allowed($parent) && get_post_type($parent) === 'rp_ticket') echo '<h2>افزودن فایل: ' . self::e(get_the_title($parent)) . '</h2>' . self::file_list($parent) . self::upload_form($parent, add_query_arg(['page' => 'riffside-account-ticket-calendar', 'rp_upload_parent' => $parent], admin_url('admin.php')));
        echo '</div>';
    }

    private static function save_settings() {
        if (!self::admin()) throw new RuntimeException('اجازه تغییر تنظیمات را ندارید.');
        $values = preg_split('/\R/', self::text('holidays', 16000)); $holidays = [];
        foreach ($values as $value) {
            $value = trim($value); if ($value === '') continue;
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) throw new RuntimeException('تاریخ تعطیلات را با قالب YYYY-MM-DD بنویسید.');
            $holidays[] = $value;
        }
        update_option('riffside_portal_holidays', array_values(array_unique($holidays)), false);
        update_option('riffside_portal_calendar_configured', 1, false);
    }
}
