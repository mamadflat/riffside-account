=== Riffside Account ===
Contributors: riffside
Requires at least: 6.5
Requires PHP: 8.0
Requires Plugins: woocommerce
Stable tag: 0.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

RTL WooCommerce account shell with store-specific palettes and private text support tickets.

== Description ==
Local pilot release. Riffside and Yadak Hamrah appearance presets, independent light/dark palettes, host font inheritance, native WooCommerce endpoints, Customer Club balance adapter and private support conversations. An existing Riffside Portal takes precedence over the standalone ticket provider.

Existing Valabi operational hooks and data are retained. Account presentation is overridden only while this plugin is active. Signed-in account routes use a standalone document calling WooCommerce's native account controller once. Theme header/footer and the storefront mobile bar are omitted on these routes. The store itself and guest login continue to use the host theme.

This release does not implement commercial license activation, private update delivery or ticket attachments. No fonts or Valabi source assets are bundled. Uninstall preserves settings and tickets.

== Installation ==
Back up the site. Install the riffside-account folder and activate with WooCommerce active. Configure appearance under the Riffside panel menu. Embedded Customer Club follows the panel appearance; its standalone settings remain independent.

== Changelog ==
= 0.4.0 =
Mobile-first home widgets, club credit and configured reward opportunities, current orders, activity and four-destination mobile navigation. Stable first paint and natural Persian customer copy.

= 0.3.0 =
Shared versioned UI foundations, host appearance bridge, synchronized theme controls and independent plugin packages. No reward rules or ledger migrations.

= 0.2.0 =
Opt-in support and license modules per site, guarded disabled routes and ticket submissions, and a redesigned settings workspace for modules, palettes and provider connections. License display delegates to the installed provider; no license engine is bundled.

= 0.1.1 =
Standalone account dashboard, desktop sidebar and accessible mobile drawer. Removed nested storefront page framing. Preserved native WooCommerce routes and the Yadak Hamrah presets.

= 0.1.0 =
Local Yadak Hamrah pilot.


