# Integration and release guide

[راهنمای اتصال فارسی](INTEGRATION.md)

## Appearance and compatibility

The panel supplies the signed-in account shell and navigation. WooCommerce remains responsible for account routing and customer data. Guest login and storefront routes use the host theme. The account document calls `wp_head`, `wp_body_open`, and `wp_footer`. Host themes can render their floating mobile navigation on `riffside_account_mobile_navigation` after the account content.

The panel’s settings supply the `--ra-*` color tokens. The bundled UI assets are version `1.0.1`, contract version `1`. The shared handle `riffside-ui-v1` is registered once. File hashes are listed in `assets/shared/riffside-ui/manifest.json`.

`assets/sections.css` loads on interior account routes. Customer Club and the dashboard keep their existing stylesheets. The panel styles license and download controls while the provider handles issuance, access, domain registration, and file delivery. Native WooCommerce actions and forms retain their existing hooks.

## Customer Club

The two plugins share a [design system](https://github.com/mamadflat/riffside-design-system) and ship independently. In the signed-in account, `customer_club_appearance_settings` passes the panel palette and light/dark policy to Customer Club. The `riffside:appearance` event and `riffside-account-mode` browser preference keep the displayed modes synchronized.

The dashboard reads balance information from `Customer_Club\Dashboard_Data::current()`. Customer Club owns reward rules and ledger writes. Outside the panel, it uses its own saved appearance settings.

## Custom tabs and extensions

Use **Custom Tabs** for text and shortcodes from active plugins. A PHP extension can register a page, widget, order section, or settings section using API version 1. For example:

```php
add_action('riffside_account_register_extensions', static function ($version) {
    if ($version !== 1) return;
    \Riffside\Account\Extensions::register('page', 'ra-example-history', [
        'title' => 'My history',
        'icon' => 'files',
        'enabled' => true,
        'render' => static function ($customer_id) {
            // Fetch and escape information scoped to this customer.
            echo '<p>' . esc_html('Your account history appears here.') . '</p>';
        },
    ]);
});
```

The extension owns its output escaping, capability and ownership checks, and nonces for writes. [Full extension API reference (Persian)](EXTENSIONS.md)

## Test

```sh
php tests/extensions.php
php tests/appearance-contract.php
php tests/dashboard.php
php tests/dashboard-legacy.php
php tests/orders.php
php tests/theme-account.php
php tests/sections.php
node --test tests/*.test.cjs
```

For browser checks, use a test customer with current, completed, and cancelled orders, along with a guest session. Check ownership, empty and populated screens, light and dark mode, direct routes, menu settings, support, downloads, and account editing. Exercise Customer Club alongside the panel and on its own.

## Build the package

Run this from the repository root. The ZIP starts with the `riffside-account/` directory. The `.gitattributes` rules exclude documentation, tests, and development configuration.

```sh
git archive --format=zip --prefix=riffside-account/ -o riffside-account-1.2.0.zip HEAD
sha256sum riffside-account-1.2.0.zip
```

Keep separate version tags and ZIPs for each plugin. To roll back, replace the current panel with the previous ZIP under the same plugin ID to retain orders, tickets, and saved settings.
