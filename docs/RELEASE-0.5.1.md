# Riffside Account 0.5.1

Customer-installable release of the independent WooCommerce account plugin.

- Shared UI 1.0.1 includes only the product palette. No originating-client preset or forced icon accent is distributed.
- Stored custom light/dark colors survive upgrades; retired preset identifiers fall back to the product theme without rewriting saved options.
- Removed storefront-specific hook removals and asset overrides. Installations needing third-party theme adaptations should keep those in a site integration plugin.
- Support and license display remain disabled until enabled by the merchant. Enabling license display requires a license provider such as Riffside License Manager; it does not issue keys itself.
- Orders, customer ownership checks, private dashboard data and independent Club integration remain unchanged.

Requires WordPress 6.5+, PHP 8.0+ and active WooCommerce. Club is optional. Install the ZIP independently; do not copy another site's database/options. Configure the merchant's colors under the account settings menu. Both plugins inherit the host font. Standalone customer account pages omit the storefront header/footer.

Validation: see the release asset QA report for the completed local integration and CI results. Existing automated checks cover appearance ownership/sanitization, standalone and legacy Club fallback, order status/pagination/ownership and the shared asset hashes.
