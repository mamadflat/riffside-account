<?php
define('ABSPATH',__DIR__);
function wc_get_order_statuses(){return array_fill_keys(['wc-pending','wc-on-hold','wc-processing','wc-completed','wc-cancelled','wc-failed','wc-refunded','wc-shipped','wc-checkout-draft'],'label');}
function is_account_page(){return $GLOBALS['account']??true;}
function is_user_logged_in(){return $GLOBALS['logged']??true;}
function is_wc_endpoint_url($endpoint){return ($GLOBALS['endpoint']??'orders')===$endpoint;}
function get_current_user_id(){return 17;}
function current_user_can($cap,$id){return $GLOBALS['capability']??true;}
function absint($n){return abs((int)$n);}
function wc_get_orders($args){$GLOBALS['queries'][]=$args;return (object)['total'=>3];}
function wc_get_page_permalink($page){return 'https://example.test/account/';}
function wc_get_endpoint_url($endpoint,$page,$base){return $base.$endpoint.'/'.$page.'/';}
function add_query_arg($args,$value=null,$url=null){if(!is_array($args))$args=[$args=>$value];else $url=$value;return $url.'?'.http_build_query($args);}
function determine_locale(){return 'fa_IR';}
function wp_timezone_string(){return 'Asia/Tehran';}
function wc_format_datetime($date){return $date->format('Y-m-d');}
require dirname(__DIR__).'/src/Dashboard.php';
require dirname(__DIR__).'/src/Orders.php';
use Riffside\Account\Orders;
function check($pass,$message){if(!$pass)throw new RuntimeException($message);}
foreach(['current'=>['wc-pending','wc-on-hold','wc-processing'],'completed'=>['wc-completed'],'other'=>['wc-cancelled','wc-failed','wc-refunded','wc-shipped']] as $group=>$statuses){
    $_GET['ra_group']=$group;$args=Orders::query(['customer'=>99,'customer_id'=>99,'limit'=>99999,'page'=>2]);
    check($args['customer']===17&&$args['customer_id']===17,'Another identity entered the order query');
    check($args['status']===$statuses&&$args['limit']===10&&$args['page']===2&&$args['paginate']===true,'Status, page or bounded list mismatch');
}
$_GET['ra_group']=['completed'];check(Orders::group()==='current','Array filter must fall back safely');
$_GET['ra_group']='cancelled,<script>';check(Orders::group()==='current','Unrecognized status accepted');
$GLOBALS['endpoint']='dashboard';check(Orders::query(['limit'=>4])===['limit'=>4],'Home queries must not be changed');$GLOBALS['endpoint']='orders';
$GLOBALS['logged']=false;check(Orders::query(['limit'=>4])===['limit'=>4],'Guest query changed');$GLOBALS['logged']=true;
Orders::counts();foreach($queries as $query)check($query['customer_id']===17&&$query['limit']===1&&$query['return']==='ids','Counts must be bounded and customer-scoped');
check(Orders::page(['9'])===1&&Orders::page(9000000)===100000,'Unbounded or malformed pagination');
$order=new class { public $owner=17;public function get_customer_id(){return $this->owner;}public function get_id(){return 5;}public function has_status($s){return $s==='completed';}};
check(Orders::owns($order),'Owned order denied');$order->owner=18;check(!Orders::owns($order),'Another customer order exposed');$order->owner=17;$GLOBALS['capability']=false;check(!Orders::owns($order),'WooCommerce capability was bypassed');
$_GET=['ra_from'=>'https://untrusted.example','ra_page'=>['999']];check(Orders::back_url($order)==='https://example.test/account/orders/1/?ra_group=current','Unsafe back URL');
$_GET=[];check(strpos(Orders::back_url($order),'ra_group=completed')!==false,'Direct detail link lost its status group');
check(Orders::date(null)==='تاریخ ثبت نشده','Missing order date failed');
if(class_exists('IntlDateFormatter'))check(Orders::date(new DateTime('2026-11-17T12:00:00Z'))==='۲۶ آبان ۱۴۰۵','Persian date or timezone failed');
echo "Order ownership, status filters, paging, safe navigation and dates passed.\n";
