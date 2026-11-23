<?php
// Portable behavior test: no WordPress database or customer data required.
define('ABSPATH', __DIR__);
define('RIFFSIDE_ACCOUNT_DIR', dirname(__DIR__) . '/');
function apply_filters($hook,$value,...$args){return $value;}
$admin = false; $account = true; $logged = true;
function is_admin() { return $GLOBALS['admin']; }
function is_account_page() { return $GLOBALS['account']; }
function is_user_logged_in() { return $GLOBALS['logged']; }
function get_option($key, $fallback = []) { return $GLOBALS['options'][$key] ?? $fallback; }
require RIFFSIDE_ACCOUNT_DIR . 'src/Appearance.php';
require RIFFSIDE_ACCOUNT_DIR . 'src/Plugin.php';
use Riffside\Account\Appearance;
use Riffside\Account\Plugin;
function check($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
$panel = Appearance::defaults();
check(array_keys(Appearance::presets()) === ['riffside'], 'No customer-specific preset is bundled');
check($panel['features'] === ['support'=>false, 'licenses'=>false], 'Optional modules are disabled on fresh installs');
$panel['theme'] = 'invalid-preset'; $panel['mode'] = 'dark';
$panel['palettes']['dark']['button'] = '#123456';
$options = [Appearance::OPTION => $panel];
$club = ['theme' => 'riffside', 'mode' => 'light', 'default_mode' => 'light', 'palettes' => [], 'extension' => 'preserved'];
$before = $options;
$effective = Plugin::club_appearance($club);
check($effective['palettes']['dark']['button'] === '#123456', 'Embedded club follows custom panel color');
check($effective['mode'] === 'dark' && $effective['extension'] === 'preserved', 'Only appearance fields are overridden');
check($effective['theme'] === 'riffside', 'Retired preset falls back without losing saved colors');
check($options === $before && $club['mode'] === 'light', 'Reading cannot persist host overrides');
foreach (['admin', 'account', 'logged'] as $context) {
    $GLOBALS[$context] = $context === 'admin';
    check(Plugin::club_appearance($club) === $club, 'Standalone/admin/guest settings stay independent');
    $GLOBALS[$context] = $context !== 'admin';
}
$unsafe = Appearance::sanitize(['mode' => ['dark'], 'palettes' => ['light' => ['button' => '#fff;}</style>', 'button_radius' => 999]], 'features' => ['support' => 'false']]);
check($unsafe['mode'] === 'both' && $unsafe['palettes']['light']['button'] === '#ff795f', 'CSS injection is rejected');
check($unsafe['palettes']['light']['button_radius'] === 24 && !$unsafe['features']['support'], 'Limits and opt-in features are enforced');
echo "Appearance ownership and sanitization checks passed.\n";
