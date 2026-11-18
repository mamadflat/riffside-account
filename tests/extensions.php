<?php
define('ABSPATH',__DIR__);define('EP_ROOT',1);define('EP_PAGES',2);
$uid=7;$account=true;$admin=false;$cap=false;$calls=[];$actions=[];$filters=[];$routes=[];$options=[];$denied=0;
function add_action($hook,$fn,$priority=10,$args=1){$GLOBALS['actions'][$hook][]=$fn;}
function add_filter($hook,$fn,$priority=10,$args=1){$GLOBALS['filters'][$hook][]=$fn;}
function do_action($hook,...$args){foreach($GLOBALS['actions'][$hook]??[] as $fn)$fn(...$args);}
function apply_filters($hook,$value,...$args){foreach($GLOBALS['filters'][$hook]??[] as $fn)$value=$fn($value,...$args);return $value;}
function add_rewrite_endpoint($id,$flags){$GLOBALS['routes'][]=$id;}
function get_option($key,$default=false){return $GLOBALS['options'][$key]??$default;}
function update_option($key,$value,$autoload=false){$GLOBALS['options'][$key]=$value;}
function flush_rewrite_rules($hard=false){$GLOBALS['flushes']=($GLOBALS['flushes']??0)+1;}
function is_admin(){return $GLOBALS['admin'];}
function is_account_page(){return $GLOBALS['account'];}
function is_user_logged_in(){return $GLOBALS['uid']>0;}
function current_user_can($cap){return $GLOBALS['cap'];}
function get_current_user_id(){return $GLOBALS['uid'];}
function esc_attr($v){return htmlspecialchars($v,ENT_QUOTES);}
function esc_html($v){return htmlspecialchars($v,ENT_QUOTES);}
function nocache_headers(){}
function wp_die($message,$title,$args){$GLOBALS['denied']=$args['response'];}
function wc_get_order($id){return $id===11?new class {function get_customer_id(){return 7;}}:($id===12?new class {function get_customer_id(){return 8;}}:false);}
require dirname(__DIR__).'/src/Extensions.php';
use Riffside\Account\Extensions as E;
function verify($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS: $label\n";}
$renderer=static function($user,$context){$GLOBALS['calls'][]=[$user,$context];echo 'SAFE';};
verify(!E::register('page','orders',['title'=>'Bad','render'=>$renderer]),'Native endpoint cannot be replaced');
verify(!E::register('page','ra-support',['title'=>'Bad','render'=>$renderer]),'Reserved module cannot be replaced');
verify(!E::register('page','ra-bad',['title'=>'Bad','render'=>'missing_function']),'Invalid renderer rejected');
verify(!E::register('page','ra-bad',['title'=>'Bad','render'=>$renderer,'enabled'=>'false']),'Ambiguous enabled value rejected');
add_filter('woocommerce_get_query_vars',static fn($v)=>$v+['external'=>'ra-collision']);
verify(!E::register('page','ra-collision',['title'=>'Bad','render'=>$renderer]),'Third-party route alias collision rejected');
add_action('riffside_account_register_extensions',static function($v)use($renderer){
 verify($v===1,'Versioned registration contract');
 E::register('page','ra-example-page',['title'=>'Example','render'=>$renderer]);
 E::register('page','ra-hidden-page',['title'=>'Hidden','enabled'=>false,'render'=>$renderer]);
 E::register('page','ra-private-page',['title'=>'Private','permission'=>static fn($id)=>$id===99,'render'=>$renderer]);
 E::register('widget','ra-example-widget',['title'=>'<unsafe>','render'=>$renderer]);
 E::register('order','ra-example-order',['title'=>'Order','render'=>$renderer]);
 E::register('settings','ra-example-settings',['title'=>'Settings','render'=>$renderer]);
});
E::boot();E::initialize();E::initialize();
verify(count($routes)===3,'Initialization and endpoint registration are idempotent');
verify(!E::register('page','ra-example-page',['title'=>'Hijack','render'=>$renderer]),'Duplicate registration cannot hijack a renderer');
verify(E::menu(['orders'=>'Orders'])===['orders'=>'Orders','ra-example-page'=>'Example'],'Menus hide disabled and forbidden pages');
verify(E::query_vars([])['ra-hidden-page']==='ra-hidden-page','Disabled routes remain guarded');
ob_start();E::page('ra-hidden-page');$out=ob_get_clean();verify($denied===404&&$out===''&&$calls===[],'Disabled direct route denies without rendering');
$wp=(object)['query_vars'=>['ra-private-page'=>'']];$denied=0;E::guard();verify($denied===404,'Forbidden direct endpoint returns 404');
ob_start();E::page('ra-example-page');ob_end_clean();verify($calls[0][0]===7,'Page gets authenticated identity');
$calls=[];ob_start();E::order_sections(12);E::order_sections(0);ob_end_clean();verify($calls===[],'Other-owner and missing orders never reach extensions');
ob_start();E::order_sections(11);ob_end_clean();verify(count($calls)===1&&$calls[0][1]->get_customer_id()===7,'Owned order reaches extension');
$calls=[];$uid=0;ob_start();E::widgets();E::order_sections(11);E::page('ra-example-page');ob_end_clean();verify($calls===[],'Guest cannot render customer content');
$uid=7;ob_start();E::widgets();$out=ob_get_clean();verify(strpos($out,'&lt;unsafe&gt;')!==false,'Extension title escaped');
$calls=[];ob_start();E::settings_sections();ob_end_clean();verify($calls===[],'Customer cannot render extension settings');
$cap=true;ob_start();E::settings_sections();ob_end_clean();verify(count($calls)===1,'Manager can render independent settings');
verify(empty($options)&&empty($GLOBALS['flushes']),'Customer reads do not persist data or flush routes');
