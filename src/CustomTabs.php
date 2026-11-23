<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class CustomTabs {
    const TYPE = 'ra_account_tab';
    const ICON_KEY = '_ra_tab_icon';

    public static function boot() {
        add_action('init', [self::class, 'register_type'], 4);
        add_action('riffside_account_register_extensions', [self::class, 'register_tabs']);
        add_action('add_meta_boxes_' . self::TYPE, [self::class, 'add_icon_box']);
        add_action('save_post_' . self::TYPE, [self::class, 'save_icon']);
        add_action('wp', [self::class, 'prepare_preview']);
    }

    public static function register_type() {
        $actions = [
            'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts',
            'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts',
            'delete_published_posts', 'delete_others_posts', 'edit_private_posts',
            'edit_published_posts', 'create_posts',
        ];
        register_post_type(self::TYPE, [
            'labels' => [
                'name' => 'تب‌های سفارشی', 'singular_name' => 'تب سفارشی',
                'add_new_item' => 'افزودن تب سفارشی', 'edit_item' => 'ویرایش تب سفارشی',
                'new_item' => 'تب سفارشی تازه', 'all_items' => 'تب‌های سفارشی',
                'add_new' => 'افزودن تب', 'not_found' => 'هنوز تبی ساخته نشده است.',
            ],
            'public' => false, 'show_ui' => true, 'show_in_menu' => 'riffside-account',
            'rewrite' => false, 'query_var' => false, 'supports' => ['title', 'editor'],
            'capabilities' => array_fill_keys($actions, 'manage_options'),
            'map_meta_cap' => false,
        ]);
    }

    public static function register_tabs() {
        $tabs = get_posts([
            'post_type' => self::TYPE, 'post_status' => 'publish',
            'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC',
        ]);
        foreach ($tabs as $tab) {
            $title = sanitize_text_field($tab->post_title);
            if ($title === '') continue;
            $icon = get_post_meta($tab->ID, self::ICON_KEY, true);
            Extensions::register('page', 'ra-tab-' . $tab->ID, [
                'title' => $title,
                'icon' => isset(self::icons()[$icon]) ? $icon : 'files',
                'render' => static fn() => self::render_content($tab),
            ]);
        }
    }

    private static function render_content($tab) {
        // Only administrators publish this markup. Shortcode output can include forms.
        echo do_shortcode(shortcode_unautop(wpautop(wp_kses_post($tab->post_content))));
    }

    public static function prepare_preview() {
        $id = $_GET['ra_tab_preview'] ?? null;
        $nonce = $_GET['_wpnonce'] ?? null;
        if (!is_string($id) || !ctype_digit($id) || !is_string($nonce) || !is_account_page()
            || !current_user_can('manage_options') || !wp_verify_nonce(wp_unslash($nonce), 'ra_preview_tab_' . $id)) return;
        $post = get_post((int) $id);
        if (!$post || $post->post_type !== self::TYPE || !in_array($post->post_status, ['draft', 'publish', 'pending', 'private'], true)) return;

        remove_action('woocommerce_account_content', 'woocommerce_account_content');
        add_action('woocommerce_account_content', static function () use ($post) {
            echo '<section class="ra-card ra-extension" data-ra-tab-preview><h1>' . esc_html($post->post_title) . '</h1>';
            self::render_content($post);
            echo '</section>';
        });
    }

    private static function icons() {
        return [
            'identity' => 'احراز هویت', 'account' => 'حساب کاربری', 'files' => 'برگه',
            'store' => 'فروشگاه', 'ticket' => 'پشتیبانی', 'download' => 'دانلود',
            'plugins' => 'افزونه', 'contact' => 'تماس', 'check' => 'تأیید',
            'clock' => 'زمان', 'corporate' => 'نشانی', 'customer-club' => 'باشگاه',
        ];
    }

    public static function add_icon_box() {
        add_meta_box('ra-tab-icon', 'آیکون منو', [self::class, 'render_icon_box'], self::TYPE, 'side');
    }

    public static function render_icon_box($post) {
        $icon = get_post_meta($post->ID, self::ICON_KEY, true) ?: 'files';
        wp_nonce_field('ra_save_tab_icon', 'ra_tab_icon_nonce');
        ?>
        <p><label for="ra-tab-icon-select">آیکون کنار عنوان تب</label></p>
        <select id="ra-tab-icon-select" name="ra_tab_icon">
            <?php foreach (self::icons() as $key => $name): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($icon, $key); ?>><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description">برای محتوای تب، متن یا شورت‌کد افزونهٔ فعال را در ویرایشگر وارد کن. پس از انتشار، ترتیب و نمایش آن را در «منو و داشبورد» تنظیم کن.</p>
        <?php if ($post->post_status !== 'auto-draft') :
            $preview = wp_nonce_url(add_query_arg('ra_tab_preview', $post->ID, wc_get_page_permalink('myaccount')), 'ra_preview_tab_' . $post->ID);
        ?>
            <p><a class="button" href="<?php echo esc_url($preview); ?>" target="_blank" rel="noopener">پیش‌نمایش محتوای ذخیره‌شده</a></p>
        <?php endif; ?>
        <?php
    }

    public static function save_icon($id) {
        $nonce = $_POST['ra_tab_icon_nonce'] ?? null;
        $icon = $_POST['ra_tab_icon'] ?? null;
        if (!is_string($nonce) || !wp_verify_nonce(wp_unslash($nonce), 'ra_save_tab_icon')
            || !current_user_can('edit_post', $id) || wp_is_post_autosave($id) || wp_is_post_revision($id)
            || !is_string($icon) || !isset(self::icons()[$icon])) return;
        update_post_meta($id, self::ICON_KEY, $icon);
    }
}
