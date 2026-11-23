<?php
namespace Riffside\Account;
defined('ABSPATH') || exit;

final class Support {
    public static function boot(){
        if(class_exists('Riffside_Portal'))return;
        add_action('init',[__CLASS__,'types']);
        add_action('woocommerce_account_ra-support_endpoint',[__CLASS__,'view']);
        add_action('admin_post_riffside_account_support',[__CLASS__,'handle']);
        add_action('admin_post_nopriv_riffside_account_support',static function(){wp_die('ابتدا وارد حساب خودت شو.','ورود لازم است',['response'=>403]);});
        add_action('admin_menu',static function(){if(Appearance::enabled('support'))add_submenu_page('riffside-account','تیکت‌های پشتیبانی','تیکت‌ها','manage_woocommerce','riffside-account-support',[__CLASS__,'admin']);});
    }
    public static function types(){
        foreach(['ra_ticket','ra_reply'] as $type)register_post_type($type,['public'=>false,'publicly_queryable'=>false,'show_ui'=>false,'show_in_rest'=>false,'exclude_from_search'=>true,'rewrite'=>false,'query_var'=>false,'can_export'=>false,'supports'=>[]]);
    }
    public static function staff(){return current_user_can('manage_woocommerce');}
    public static function allowed($ticket){return is_user_logged_in()&&$ticket&&$ticket->post_type==='ra_ticket'&&$ticket->post_status==='private'&&(self::staff()||(int)$ticket->post_author===get_current_user_id());}
    public static function count($user_id){$q=new \WP_Query(['post_type'=>'ra_ticket','post_status'=>'private','author'=>(int)$user_id,'posts_per_page'=>1,'fields'=>'ids']);return (int)$q->found_posts;}
    private static function url($id=0,$admin=false){$url=$admin?admin_url('admin.php?page=riffside-account-support'):Plugin::url('ra-support');return $id?add_query_arg('ticket',$id,$url):$url;}
    private static function form($op,$id=0,$admin=false){
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="ra-support-form"><input type="hidden" name="action" value="riffside_account_support"><input type="hidden" name="operation" value="'.esc_attr($op).'"><input type="hidden" name="ticket" value="'.(int)$id.'"><input type="hidden" name="request_token" value="'.esc_attr(wp_generate_uuid4()).'">';
        if($admin)echo '<input type="hidden" name="admin_return" value="1">';wp_nonce_field('riffside_account_support','_ra_nonce');
    }
    private static function text($key,$max){$s=isset($_POST[$key])&&is_string($_POST[$key])?sanitize_textarea_field(wp_unslash($_POST[$key])):'';return mb_substr(trim($s),0,$max);}
    private static function notice(){ $key='ra_notice_'.get_current_user_id();$notice=get_transient($key);if($notice){delete_transient($key);echo '<p class="ra-notice" role="status">'.esc_html($notice).'</p>';}}
    public static function handle(){
        if(!Appearance::enabled('support'))wp_die('تیکت در این فروشگاه فعال نیست.','بخش غیرفعال',['response'=>403]);
        if(!is_user_logged_in())wp_die('ورود لازم است.','',['response'=>403]);
        check_admin_referer('riffside_account_support','_ra_nonce');
        $op=self::text('operation',20);$id=isset($_POST['ticket'])?absint($_POST['ticket']):0;$ticket=$id?get_post($id):null;$admin=self::staff()&&!empty($_POST['admin_return']);
        if(!in_array($op,['create','reply','close'],true)||($op!=='create'&&!self::allowed($ticket)))wp_die('دسترسی مجاز نیست.','',['response'=>403]);
        if($op==='reply'&&get_post_meta($id,'_ra_status',true)==='closed')wp_die('این تیکت بسته شده است.','',['response'=>409]);
        $body=self::text('message',5000);$subject=self::text('subject',160);
        if(($op==='create'&&($subject===''||$body===''))||($op==='reply'&&$body===''))wp_die('موضوع و پیام را کامل بنویس.','',['response'=>400]);
        $order_id=isset($_POST['order_id'])?absint($_POST['order_id']):0;
        if($op==='create'&&$order_id){$order=wc_get_order($order_id);if(!$order||(int)$order->get_customer_id()!==get_current_user_id())wp_die('سفارش متعلق به این حساب نیست.','',['response'=>403]);}
        $token=self::text('request_token',40);if(!preg_match('/^[a-f0-9-]{36}$/D',$token))wp_die('درخواست معتبر نیست.','',['response'=>400]);
        $token_key='ra_submit_'.get_current_user_id().'_'.hash('sha256',$token);
        $existing=get_option($token_key,false);if($existing!==false){wp_safe_redirect(self::url((int)$existing,$admin),303);exit;}
        $rate_key='ra_rate_'.get_current_user_id();$rate=(int)get_transient($rate_key);if($rate>=10)wp_die('لطفاً کمی بعد دوباره تلاش کن.','',['response'=>429]);
        if(!add_option($token_key,0,'',false)){wp_safe_redirect(self::url($id,$admin),303);exit;}
        set_transient($rate_key,$rate+1,60);
        if($op==='create'){
            $id=wp_insert_post(['post_type'=>'ra_ticket','post_status'=>'private','post_author'=>get_current_user_id(),'post_title'=>$subject,'post_content'=>$body],true);
            if(is_wp_error($id)){delete_option($token_key);wp_die('ثبت تیکت انجام نشد؛ دوباره تلاش کن.','',['response'=>500]);}
            update_post_meta($id,'_ra_status','open');if($order_id)update_post_meta($id,'_ra_order',$order_id);$message='تیکت ثبت شد. پاسخ را در همین بخش دنبال کن.';
        }elseif($op==='reply'){
            $reply=wp_insert_post(['post_type'=>'ra_reply','post_status'=>'private','post_author'=>get_current_user_id(),'post_parent'=>$id,'post_content'=>$body],true);
            if(is_wp_error($reply)){delete_option($token_key);wp_die('ثبت پاسخ انجام نشد.','',['response'=>500]);}
            update_post_meta($id,'_ra_status',self::staff()?'answered':'open');update_post_meta($reply,'_ra_staff',self::staff()?1:0);$message='پاسخ ثبت شد.';
        }else{update_post_meta($id,'_ra_status','closed');$message='تیکت بسته شد.';}
        update_option($token_key,$id,false);set_transient('ra_notice_'.get_current_user_id(),$message,120);
        // Notifications use this store's recipient.
        if($op!=='close'){$ticket=get_post($id);$owner=get_userdata($ticket->post_author);$to=self::staff()&&$op==='reply'&&$owner?$owner->user_email:get_option('admin_email');$url=self::staff()&&$op==='reply'?self::url($id):self::url($id,true);wp_mail($to,'پیام پشتیبانی — '.wp_specialchars_decode(get_bloginfo('name'),ENT_QUOTES),'گفت‌وگوی پشتیبانی به‌روز شد: '.$url);}
        wp_safe_redirect(self::url($id,$admin),303);exit;
    }
    public static function view($admin=false){
        if(!Appearance::enabled('support'))return;
        if(!is_user_logged_in())return;
        $admin=$admin===true&&self::staff();$id=isset($_GET['ticket'])?absint($_GET['ticket']):0;
        self::notice();
        if (!is_account_page()) echo '<div class="ra-page-head"><h1>پشتیبانی</h1><p>گفت‌وگو دربارهٔ خرید و محصولات</p></div>';
        if($id){$ticket=get_post($id);if(!self::allowed($ticket)){echo '<p class="ra-notice">تیکت پیدا نشد یا دسترسی به آن مجاز نیست.</p>';return;}
            echo '<section class="ra-card"><div class="ra-card-head"><h2>'.esc_html($ticket->post_title).'</h2><a href="'.esc_url(self::url(0,$admin)).'">همهٔ تیکت‌ها</a></div><div class="ra-message">'.nl2br(esc_html($ticket->post_content)).'<small>پیام آغاز گفت‌وگو</small></div>';
            $page=max(1,absint($_GET['replies_page']??1));$replies=new \WP_Query(['post_type'=>'ra_reply','post_status'=>'private','post_parent'=>$id,'posts_per_page'=>30,'paged'=>$page,'orderby'=>'ID','order'=>'DESC']);
            foreach(array_reverse($replies->posts) as $reply){$staff=(bool)get_post_meta($reply->ID,'_ra_staff',true);echo '<div class="ra-message '.($staff?'ra-message-staff':'').'">'.nl2br(esc_html($reply->post_content)).'<small>'.($staff?'تیم پشتیبانی':'مشتری').' · '.esc_html(get_the_date('Y/m/d H:i',$reply)).'</small></div>';}
            if($replies->max_num_pages>1)echo wp_kses_post(paginate_links(['base'=>add_query_arg('replies_page','%#%',self::url($id,$admin)),'format'=>'','current'=>$page,'total'=>$replies->max_num_pages,'prev_text'=>'جدیدتر','next_text'=>'قدیمی‌تر']));
            if(get_post_meta($id,'_ra_status',true)==='closed'){echo '<p class="ra-notice">این گفت‌وگو بسته شده است.</p>';}else{
                self::form('reply',$id,$admin);echo '<label for="ra-message">پاسخ شما</label><textarea id="ra-message" name="message" required maxlength="5000" rows="5"></textarea><button class="ra-button ra-primary" type="submit">ارسال پاسخ</button></form>';
                self::form('close',$id,$admin);echo '<button type="submit" class="ra-button">بستن تیکت</button></form>';
            }echo '</section>';return;
        }
        $page=max(1,absint($_GET['support_page']??1));$args=['post_type'=>'ra_ticket','post_status'=>'private','posts_per_page'=>20,'paged'=>$page,'orderby'=>'ID','order'=>'DESC'];if(!$admin)$args['author']=get_current_user_id();$q=new \WP_Query($args);
        echo '<section class="ra-card"><h2>گفت‌وگوهای '.($admin?'مشتریان':'من').'</h2>';
        if(!$q->posts)echo '<p class="ra-empty">هنوز تیکتی ثبت نشده است.</p>';
        foreach($q->posts as $ticket){$status=get_post_meta($ticket->ID,'_ra_status',true);echo '<article class="ra-order ra-row"><a href="'.esc_url(self::url($ticket->ID,$admin)).'">'.esc_html($ticket->post_title).'</a><span class="ra-badge">'.esc_html(['open'=>'در انتظار پاسخ','answered'=>'پاسخ داده‌شده','closed'=>'بسته‌شده'][$status]??'در انتظار پاسخ').'</span></article>';}
        echo wp_kses_post(paginate_links(['base'=>add_query_arg('support_page','%#%',self::url(0,$admin)),'format'=>'','current'=>$page,'total'=>$q->max_num_pages,'prev_text'=>'قبلی','next_text'=>'بعدی']));echo '</section>';
        if(!$admin){echo '<section class="ra-card" id="ra-new-ticket"><h2>تیکت جدید</h2>';self::form('create');echo '<label for="ra-subject">موضوع</label><input id="ra-subject" name="subject" required maxlength="160"><label for="ra-related-order">سفارش مرتبط</label><select id="ra-related-order" name="order_id"><option value="0">پرسش عمومی</option>';foreach(wc_get_orders(['customer_id'=>get_current_user_id(),'limit'=>30]) as $order)echo '<option value="'.(int)$order->get_id().'">'.esc_html('سفارش '.$order->get_order_number()).'</option>';echo '</select><label for="ra-message">پیام شما</label><textarea id="ra-message" name="message" required rows="5" maxlength="5000"></textarea><button class="ra-button ra-primary" type="submit">ثبت تیکت</button></form></section>';}
    }
    public static function admin(){if(!self::staff()||!Appearance::enabled('support'))return;echo '<div class="wrap ra-admin"><h1>تیکت‌های پنل ریف‌ساید</h1>';self::view(true);echo '</div>';}
}
