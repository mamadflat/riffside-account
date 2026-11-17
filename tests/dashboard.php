<?php
define('ABSPATH',__DIR__);
function wp_get_current_user(){return (object)['ID'=>17];}
function wc_get_orders($args){$GLOBALS['query']=$args;return (object)['orders'=>['current-order'],'total'=>5];}
function wc_get_customer_order_count($id){if($id!==17)throw new RuntimeException('Wrong customer');return 9;}
require dirname(__DIR__).'/src/Dashboard.php';
$data=\Riffside\Account\Dashboard::data();
if($query['customer_id']!==17||$query['limit']!==2||!$query['paginate']||$query['status']!==['wc-pending','wc-on-hold','wc-processing'])throw new RuntimeException('Order scope or current status set changed');
if($data['active_count']!==5||$data['order_count']!==9||$data['club']['available']!==false)throw new RuntimeException('Standalone counts or optional club failed');
if(\Riffside\Account\Dashboard::digits('1,250')!=='۱,۲۵۰')throw new RuntimeException('Persian display digits failed');
echo "Standalone dashboard and bounded customer order query passed.\n";
