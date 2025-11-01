<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Loader {
  public static function init() {
    add_shortcode( 'cdb_press_portal', array( __CLASS__, 'shortcode_portal' ) );
    add_action( 'rest_api_init', array( __CLASS__, 'rest' ) );
  }

  public static function rest() {
    register_rest_route( 'cdb/v1', '/new', array(
      'methods' => 'GET',
      'permission_callback' => '__return_true',
      'callback' => function( WP_REST_Request $req ){
        $page = max(1, intval($req->get_param('page')));
        $per  = max(1, intval($req->get_param('per_page')));
        $q = new WP_Query(array(
          'post_type'=>'post',
          'post_status'=>'publish',
          'paged'=>$page,
          'posts_per_page'=>$per,
          'orderby'=>'date',
          'order'=>'DESC',
        ));
        ob_start();
        CDB_PP_Loader::loop_cards( $q );
        $html = ob_get_clean();
        return new WP_REST_Response(array( 'html'=>$html, 'max_num_pages'=>intval($q->max_num_pages) ), 200 );
      }
    ));
  }

  public static function shortcode_portal( $atts = array() ) {
    $pickup = intval( get_option('cdb_pp_pickup_limit', 6 ) );
    $newper = intval( get_option('cdb_pp_new_limit', 12 ) );

    ob_start();
    include CDB_PP_DIR . 'templates/section-hero.php';
    echo do_shortcode('[cdb_press_filter]');

    echo '<section class="cdb-pp-section"><div class="cdb-pp-section-hd"><h3>ピックアップ</h3></div>';
    $q = new WP_Query(array(
      'post_type'=>'post','post_status'=>'publish','posts_per_page'=>$pickup,
      'date_query'=>array(array( 'after'=> date('Y-m-d', strtotime('-2 months')), 'inclusive'=>true )),
      'orderby'=>'rand'
    ));
    echo '<div class="cdb-pp-grid">'; self::loop_cards($q); echo '</div>'; wp_reset_postdata();
    echo '</section>';

    echo '<section class="cdb-pp-section cdb-pp-new" data-page="1" data-per-page="'.esc_attr($newper).'"><div class="cdb-pp-section-hd"><h3>新着</h3></div>';
    $q2 = new WP_Query(array( 'post_type'=>'post','post_status'=>'publish','posts_per_page'=>$newper,'orderby'=>'date','order'=>'DESC' ));
    echo '<div class="cdb-pp-grid">'; self::loop_cards($q2); echo '</div>';
    if ( $q2->max_num_pages > 1 ) { echo '<button class="cdb-pp-more">'.esc_html__('もっと見る','cdb-press-portal').'</button>'; }
    echo '</section>'; wp_reset_postdata();

    echo '<section class="cdb-pp-section"><div class="cdb-pp-section-hd"><h3>SNSで話題</h3></div>';
    echo do_shortcode('[cdb_press_social_hot limit="10"]');
    echo '</section>';

    echo '<section class="cdb-pp-section"><div class="cdb-pp-section-hd"><h3>今日のランキング</h3></div>';
    echo do_shortcode('[cdb_press_today_rank limit="10"]');
    echo '</section>';
    echo '<section class="cdb-pp-section"><div class="cdb-pp-section-hd"><h3>今月のランキング</h3></div>';
    echo do_shortcode('[cdb_press_month_rank limit="10"]');
    echo '</section>';

    return ob_get_clean();
  }

  public static function loop_cards( $q ) {
    if ( $q->have_posts() ) {
      while( $q->have_posts() ) { $q->the_post();
        $pid = get_the_ID();
        $meta_key = get_option('cdb_pp_company_meta_key','_cdb_company_id');
        $company_id = intval( get_post_meta( $pid, $meta_key, true ) );
        $company = $company_id ? get_post( $company_id ) : null;
        echo '<article class="cdb-pp-card" data-post-id="'.esc_attr($pid).'">';
        echo '<a class="cdb-pp-thumbwrap" href="'.esc_url(get_permalink()).'">';
        if ( has_post_thumbnail() ) { the_post_thumbnail('medium_large'); }
        echo '</a>';
        echo '<div class="cdb-pp-meta">';
        if ( $company ) { echo '<span class="cdb-pp-company">'.esc_html(get_the_title($company)).'</span>'; }
        echo '<time class="cdb-pp-time" datetime="'.esc_attr(get_the_date('c')).'">'.esc_html(get_the_date()).'</time>';
        echo '</div>';
        echo '<h4 class="cdb-pp-title"><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h4>';
        echo '<div class="cdb-pp-share">';
        echo '<button class="cdb-pp-share-btn" data-network="x" data-post-id="'.get_the_ID().'" data-url="'.esc_url(get_permalink()).'" title="X">X</button>';
        echo '<button class="cdb-pp-share-btn" data-network="facebook" data-post-id="'.get_the_ID().'" data-url="'.esc_url(get_permalink()).'" title="Facebook">Facebook</button>';
        echo '<button class="cdb-pp-share-btn" data-network="line" data-post-id="'.get_the_ID().'" data-url="'.esc_url(get_permalink()).'" title="LINE">LINE</button>';
        echo '</div>';
        echo '</article>';
      }
    } else {
      echo '<p class="cdb-pp-empty">'.esc_html__('該当する記事がありません。','cdb-press-portal').'</p>';
    }
  }
}
