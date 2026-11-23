=== Riffside Account ===
Contributors: riffside
Requires at least: 6.5
Requires PHP: 8.0
Requires Plugins: woocommerce
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free RTL WooCommerce customer account with customizable palettes, custom tabs, and optional support tickets.

== Description ==
Riffside Account adds a customer dashboard, order details, a mobile account drawer, editable light and dark palettes, and a configurable menu with custom tabs. It uses WooCommerce account routes and customer data. Optional support uses private tickets and can display existing Riffside Portal conversations and encrypted attachments on sites that use that integration. Compatible license providers handle keys, domain registration, and plugin downloads.

Account presentation is overridden only while this plugin is active. Site-specific compatibility overrides are not bundled. Signed-in account routes use a standalone document calling WooCommerce's native account controller once. Theme header/footer and the storefront mobile bar are omitted on these routes. The store itself and guest login continue to use the host theme.

The plugin inherits the host theme's font. Uninstall preserves settings and tickets.

== Installation ==
Back up the site. Install the riffside-account folder and activate with WooCommerce active. Configure appearance under the Riffside panel menu. Embedded Customer Club follows the panel appearance; its standalone settings remain independent.

== Changelog ==
= 1.2.0 =
Refresh account orders, support, licenses, downloads and profile forms on desktop and mobile. Existing endpoints and extension hooks are retained.

= 1.1.1 =
Add an on-demand mobile account preview and retain the provider's license/download label in dashboard shortcuts.

= 1.1.0 =
Custom account tabs with a WordPress editor, shortcodes and icons. Configure menu order and visibility with a live dashboard preview. Added profile and order widgets, a mobile menu theme control, compact shortcuts and color-coded order statuses.

= 1.0.5 =
Compact order confirmation and receipt for classic WooCommerce checkout.

= 1.0.4 =
Mobile account drawer and optional host-provided navigation.

= 1.0.3 =
Dashboard focused on the current or most recent order and account shortcuts.

= 1.0.2 =
Dedicated portal ticket and encrypted attachment handling.

= 1.0.0 =
Rebuilt shared account with optional ticket and prepaid license presentation modules, a separate appearance layer, and a Customer Club add-on catalog. Existing account settings and WooCommerce data are preserved.

= 0.6.1 =
WoodMart and Flatsome account compatibility. Use one navigation and dashboard without theme wrappers or duplicate shortcut links. Keep WooCommerce endpoints and third-party account hooks.

= 0.6.0 =
Versioned extension API for private pages, menu items, dashboard widgets, owned-order sections and independent settings. Preset/CSS extension filters. Site-specific shipment tracking moved out of core.

= 0.5.1 =
Customer-ready generic package. Remove client-specific presets, forced icon colors and theme/plugin hook overrides. Preserve saved custom palettes. Shared UI 1.0.1; optional modules remain opt-in.

= 0.5.0 =
Mobile order cards with current/completed/other filters, persistent pagination and a readable order detail layout. Native totals, partial refunds, actions, notes, downloads and extension hooks retained.

= 0.4.0 =
Mobile-first home widgets, club credit and configured reward opportunities, current orders, activity and four-destination mobile navigation. Stable first paint and natural Persian customer copy.

= 0.3.0 =
Shared versioned UI foundations, host appearance bridge, synchronized theme controls and independent plugin packages. No reward rules or ledger migrations.

= 0.2.0 =
Opt-in support and license modules per site, guarded disabled routes and ticket submissions, and a redesigned settings workspace for modules, palettes and provider connections. License display delegates to the installed provider; no license engine is bundled.

= 0.1.1 =
Standalone account dashboard, desktop sidebar and accessible mobile drawer. Removed nested storefront page framing. Preserved native WooCommerce routes and configurable palettes.

= 0.1.0 =
Initial local pilot.
