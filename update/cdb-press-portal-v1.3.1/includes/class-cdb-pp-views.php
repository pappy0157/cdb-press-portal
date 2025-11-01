<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class CDB_PP_Views {
  public static function init() { add_action( 'template_redirect', [ __CLASS__, 'bump' ] ); }
  protected static function get_ip_hash(){ $ip=$_SERVER['REMOTE_ADDR']??''; return $ip?substr(md5($ip.wp_unslash($_SERVER['HTTP_USER_AGENT']??'')),0,16):''; }
  public static function bump() {
    if ( ! is_singular('post') ) return; global $post; if ( ! $post || $post->post_type !== 'post' ) return;
    $pid=$post->ID; $today=current_time('Ymd'); $month=current_time('Ym'); $iphash=self::get_ip_hash();
    if($iphash){ $key='cdbpp_seen_'.$pid.'_'.$iphash; if(get_transient($key)) return; set_transient($key,1,15*MINUTE_IN_SECONDS); }
    $total=(int)get_post_meta($pid,'_views',true); update_post_meta($pid,'_views',$total+1);
    $last_day=get_post_meta($pid,'_views_day',true); if($last_day!==$today){ update_post_meta($pid,'_today_views',0); update_post_meta($pid,'_views_day',$today); }
    $tv=(int)get_post_meta($pid,'_today_views',true); update_post_meta($pid,'_today_views',$tv+1);
    $last_month=get_post_meta($pid,'_views_month',true); if($last_month!==$month){ update_post_meta($pid,'_monthly_views',0); update_post_meta($pid,'_views_month',$month); }
    $mv=(int)get_post_meta($pid,'_monthly_views',true); update_post_meta($pid,'_monthly_views',$mv+1);
  }
}