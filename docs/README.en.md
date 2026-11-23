# Riffside Account for WooCommerce

[راهنمای فارسی](../README.md)

Riffside Account is a free, open-source WooCommerce customer account plugin. **Version 1.2.0** refreshes interior account screens in light and dark mode and uses the host theme’s font.

## Install or update

1. Use WordPress 6.5+, PHP 8.0+, and an active WooCommerce installation.
2. Download the installable ZIP from [GitHub Releases](https://github.com/mamadflat/riffside-account/releases) or [build it from source](INTEGRATION.en.md#build-the-package).
3. In WordPress, go to **Plugins → Add New Plugin → Upload Plugin**. When updating an existing installation, choose to replace the installed version.
4. Open **Riffside Panel** to configure the palette, theme policy, and optional modules.

## Customer account

- Dashboard with the customer’s name and billing phone, an edit account link, order summaries, and compact shortcuts.
- Current, completed, and other orders, with status badges, line items, payment totals, and WooCommerce actions.
- Support inbox, new ticket form, and replies. Sites already using Riffside Portal can display existing conversations and encrypted attachments through the panel.
- Native WooCommerce downloads, profile, addresses, and saved payment methods. License keys and domain management require a compatible license provider.
- Theme switching in the mobile account drawer when the administrator allows both light and dark modes.

## Admin settings

- **Riffside Panel → Menu and Dashboard** controls menu order and visibility with an immediate preview. Home and Log out stay available. The mobile account preview uses saved settings.
- **Riffside Panel → Custom Tabs** lets WordPress administrators set a title and icon and add text or active-plugin shortcodes in the editor. Administrators can preview saved drafts. Published tabs are available inside authenticated customer accounts.
- **Riffside Panel → Add-ons** shows the installation and activation options for [Customer Club](https://github.com/mamadflat/customer-club). Customer Club has its own package, settings, and release cycle.

## Development

- [Version 1.2.0 release notes and verification](RELEASE-1.2.0.md)
- [Integration, testing, and packaging](INTEGRATION.en.md)
- [Extension API and example (Persian)](EXTENSIONS.md)
- [GPL-2.0-or-later license](../LICENSE.txt)
