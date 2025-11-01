<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class CDB_PP_Social {
  public static function init() {
    add_action( 'rest_api_init', [ __CLASS__, 'rest' ] );
    add_shortcode( 'cdb_press_social_hot', [ __CLASS__, 'shortcode_social_hot' ] );
    add_action( 'cdb_pp_cron_decay_social_score', [ __CLASS__, 'decay' ] );
  }
  public static function rest() {
    register_rest_route( 'cdb/v1', '/social-hit', [
      'methods'  => 'POST','permission_callback' => '__return_true',
      'callback' => function( WP_REST_Request $req ) {
        $post_id = (int)$req->get_param('post_id');
        if ( ! $post_id || get_post_type($post_id) !== 'post' ) return new WP_REST_Response(['ok'=>false], 400);
        $score = (int) get_post_meta( $post_id, '_social_score', true ); $score++;
        update_post_meta( $post_id, '_social_score', $score );
        return new WP_REST_Response(['ok'=>true,'score'=>$score], 200);
      }
    ]);
    register_rest_route( 'cdb/v1', '/filter', [
      'methods' => 'GET','permission_callback' => '__return_true',
      'callback' => [ __CLASS__, 'rest_filter' ]
    ]);
  }
  public static function hatena_count( $url ) {
    if ( ! get_option('cdb_pp_enable_hatena') ) return 0;
    $api = 'https://bookmark.hatenaapis.com/count/entry?url=' . rawurlencode( $url );
    $res = wp_remote_get( $api, [ 'timeout' => 3 ] );
    if ( is_wp_error($res) ) return 0;
    $body = trim( wp_remote_retrieve_body( $res ) );
    return is_numeric($body) ? (int)$body : 0;
  }
  public static function decay() {
    $q = new WP_Query([ 'post_type'=>'post','post_status'=>'publish','posts_per_page'=>200,'orderby'=>'meta_value_num','meta_key'=>'_social_score','order'=>'DESC' ]);
    if ( $q->have_posts() ) { while( $q->have_posts() ){ $q->the_post();
      $s = (int) get_post_meta( get_the_ID(), '_social_score', true );
      if ( $s > 1 ) update_post_meta( get_the_ID(), '_social_score', floor($s * 0.98) );
    } } wp_reset_postdata();
  }
  public static function shortcode_social_hot( $atts = [] ) {
    $atts = shortcode_atts([ 'limit'=>10 ], $atts );
    $q = new WP_Query([
      'post_type'=>'post','post_status'=>'publish','posts_per_page'=>(int)$atts['limit'],
      'orderby'=>'meta_value_num','order'=>'DESC','meta_key'=>'_social_score',
      'date_query'=>[[ 'after'=> date('Y-m-d', strtotime('-30 days')), 'inclusive'=>true ]],
    ]);
    ob_start();
    echo '<div class="cdb-pp-social-hot">';
    if ( $q->have_posts() ) {
      echo '<ol class="cdb-pp-list">';
      while( $q->have_posts() ){ $q->the_post();
        $thumb = get_the_post_thumbnail( get_the_ID(), 'thumbnail', [ 'class'=>'cdb-pp-list-thumb' ] );
        $score = (int) get_post_meta( get_the_ID(), '_social_score', true );
        $link  = esc_url( get_permalink() );
        echo '<li class="cdb-pp-list-item">';
        echo '<a class="cdb-pp-list-media" href="'.$link.'">'.($thumb ?: '<span class="cdb-pp-noimg"></span>').'</a>';
        echo '<div class="cdb-pp-list-body">';
        echo '<a class="cdb-pp-list-title" href="'.$link.'">'.esc_html(get_the_title()).'</a>';
        echo '<div class="cdb-pp-list-meta"><span class="cdb-pp-rank-views">'.number_format_i18n($score).'</span></div>';
        echo '</div>';
        echo '</li>';
      }
      echo '</ol>';
    } else { echo '<p class="cdb-pp-empty">'.esc_html__('SNS話題データがありません。','cdb-press-portal').'</p>'; }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
  }
  public static function rest_filter( WP_REST_Request $req ) {
    $cat  = sanitize_text_field( $req->get_param('category') );
    $tag  = sanitize_text_field( $req->get_param('tag') );
    $s    = sanitize_text_field( $req->get_param('s') );
    $page = max(1, (int)$req->get_param('page'));
    $args = [ 'post_type'=>'post','post_status'=>'publish','paged'=>$page,'posts_per_page'=>12 ];
    if ( $cat ) $args['category_name'] = $cat;
    if ( $tag ) $args['tag'] = $tag;
    if ( $s )   $args['s'] = $s;
    $q = new WP_Query( $args );
    ob_start(); CDB_PP_Loader::loop_cards( $q ); $html = ob_get_clean();
    return new WP_REST_Response([ 'html'=>$html, 'max_num_pages'=>(int)$q->max_num_pages ], 200 );
  }
}
