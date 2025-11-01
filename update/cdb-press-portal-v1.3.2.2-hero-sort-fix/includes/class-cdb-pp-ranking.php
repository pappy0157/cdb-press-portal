<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class CDB_PP_Ranking {
  public static function init() {
    add_shortcode( 'cdb_press_today_rank', array( __CLASS__, 'shortcode_today_rank' ) );
    add_shortcode( 'cdb_press_month_rank', array( __CLASS__, 'shortcode_month_rank' ) );
  }
  protected static function list_item_with_thumb( $views_label_cb ) {
    $thumb = get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'class'=>'cdb-pp-list-thumb' ) );
    $title = esc_html( get_the_title() );
    $link  = esc_url( get_permalink() );
    $views_html = $views_label_cb ? call_user_func( $views_label_cb, get_the_ID() ) : '';
    echo '<li class="cdb-pp-list-item">';
    echo '<a class="cdb-pp-list-media" href="'.$link.'">'.($thumb ?: '<span class="cdb-pp-noimg"></span>').'</a>';
    echo '<div class="cdb-pp-list-body">';
    echo '<a class="cdb-pp-list-title" href="'.$link.'">'.$title.'</a>';
    if ( $views_html ) echo '<div class="cdb-pp-list-meta">'.$views_html.'</div>';
    echo '</div>';
    echo '</li>';
  }
  public static function shortcode_today_rank( $atts = array() ) {
    $atts = shortcode_atts(array( 'limit' => 10 ), $atts );
    $q = new WP_Query(array( 'post_type'=>'post','post_status'=>'publish','posts_per_page'=>intval($atts['limit']),'orderby'=>'meta_value_num','order'=>'DESC','meta_key'=>'_today_views' ));
    ob_start();
    echo '<ol class="cdb-pp-list cdb-pp-rank">';
    if ( $q->have_posts() ) { while ( $q->have_posts() ) { $q->the_post();
      self::list_item_with_thumb( function( $post_id ){ $v=intval(get_post_meta($post_id,'_today_views',true)); return '<span class="cdb-pp-rank-views">'.number_format_i18n($v).'</span>'; });
    } } else { echo '<li class="cdb-pp-list-item">'.esc_html__('ランクデータがありません。','cdb-press-portal').'</li>'; }
    echo '</ol>'; wp_reset_postdata(); return ob_get_clean();
  }
  public static function shortcode_month_rank( $atts = array() ) {
    $atts = shortcode_atts(array( 'limit' => 10 ), $atts );
    $q = new WP_Query(array( 'post_type'=>'post','post_status'=>'publish','posts_per_page'=>intval($atts['limit']),'orderby'=>'meta_value_num','order'=>'DESC','meta_key'=>'_monthly_views' ));
    ob_start();
    echo '<ol class="cdb-pp-list cdb-pp-rank">';
    if ( $q->have_posts() ) { while ( $q->have_posts() ) { $q->the_post();
      self::list_item_with_thumb( function( $post_id ){ $v=intval(get_post_meta($post_id,'_monthly_views',true)); return '<span class="cdb-pp-rank-views">'.number_format_i18n($v).'</span>'; });
    } } else { echo '<li class="cdb-pp-list-item">'.esc_html__('ランクデータがありません。','cdb-press-portal').'</li>'; }
    echo '</ol>'; wp_reset_postdata(); return ob_get_clean();
  }
}