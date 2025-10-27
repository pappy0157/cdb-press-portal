<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Ranking {
  public static function init() {}

  public static function shortcode_today_rank( $atts = [] ) {
    $atts = shortcode_atts([ 'limit' => 10 ], $atts );
    $args = [
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => (int)$atts['limit'],
      'orderby' => 'meta_value_num',
      'order'   => 'DESC',
      'meta_key'=> '_today_views',
      'date_query' => [[ 'after' => date('Y-m-d 00:00:00'), 'inclusive'=>true ]],
    ];
    $args = apply_filters( 'cdb_pp_today_rank_query_args', $args );
    $q = new WP_Query($args);

    ob_start();
    echo '<ol class="cdb-pp-rank">';
    if ( $q->have_posts() ) {
      while ( $q->have_posts() ) { $q->the_post();
        $views = (int) get_post_meta( get_the_ID(), '_today_views', true );
        echo '<li><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a><span class="cdb-pp-rank-views">'.number_format_i18n($views).'</span></li>';
      }
    } else {
      echo '<li>'.esc_html__('ランクデータがありません。','cdb-press-portal').'</li>';
    }
    echo '</ol>';
    wp_reset_postdata();
    return ob_get_clean();
  }

  public static function shortcode_month_rank( $atts = [] ) {
    $atts = shortcode_atts([ 'limit' => 10 ], $atts );
    $start = date('Y-m-01 00:00:00');
    $args = [
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => (int)$atts['limit'],
      'orderby' => 'meta_value_num',
      'order'   => 'DESC',
      'meta_key'=> '_monthly_views',
      'date_query' => [[ 'after' => $start, 'inclusive'=>true ]],
    ];
    $args = apply_filters( 'cdb_pp_month_rank_query_args', $args );
    $q = new WP_Query($args);

    ob_start();
    echo '<ol class="cdb-pp-rank">';
    if ( $q->have_posts() ) {
      while ( $q->have_posts() ) { $q->the_post();
        $views = (int) get_post_meta( get_the_ID(), '_monthly_views', true );
        echo '<li><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a><span class="cdb-pp-rank-views">'.number_format_i18n($views).'</span></li>';
      }
    } else {
      echo '<li>'.esc_html__('ランクデータがありません。','cdb-press-portal').'</li>';
    }
    echo '</ol>';
    wp_reset_postdata();
    return ob_get_clean();
  }
}
