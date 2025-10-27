<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Loader {
  public static function init() {
    add_shortcode( 'cdb_press_portal', [ __CLASS__, 'shortcode_portal' ] );
    add_shortcode( 'cdb_press_pickup', [ __CLASS__, 'shortcode_pickup' ] );
    add_shortcode( 'cdb_press_new',    [ __CLASS__, 'shortcode_new' ] );
    add_shortcode( 'cdb_press_today_rank', [ 'CDB_PP_Ranking', 'shortcode_today_rank' ] );
    add_shortcode( 'cdb_press_month_rank', [ 'CDB_PP_Ranking', 'shortcode_month_rank' ] );
    add_shortcode( 'cdb_press_taxonomy_list', [ __CLASS__, 'shortcode_taxonomy_list' ] );
    add_shortcode( 'cdb_press_social_hot', [ 'CDB_PP_Social', 'shortcode_social_hot' ] );
    add_shortcode( 'cdb_press_filter', [ 'CDB_PP_Filter', 'shortcode_filter' ] );
    add_shortcode( 'cdb_press_companies', [ 'CDB_PP_Company', 'shortcode_company_block' ] );

    add_action( 'rest_api_init', [ __CLASS__, 'register_rest' ] );
  }

  public static function register_rest() {
    register_rest_route( 'cdb/v1', '/new', [
      'methods' => 'GET',
      'callback' => [ __CLASS__, 'rest_new' ],
      'permission_callback' => '__return_true',
    ]);
  }

  public static function shortcode_portal( $atts = [] ) {
    wp_enqueue_style( 'cdb-pp-style' );
    wp_enqueue_script( 'cdb-pp-script' );

    ob_start();
    include CDB_PP_DIR . 'templates/section-hero.php';
    include CDB_PP_DIR . 'templates/section-pickup.php';
    include CDB_PP_DIR . 'templates/section-new.php';
    include CDB_PP_DIR . 'templates/section-social.php';
    include CDB_PP_DIR . 'templates/section-company.php';
    include CDB_PP_DIR . 'templates/section-today-rank.php';
    include CDB_PP_DIR . 'templates/section-month-rank.php';
    include CDB_PP_DIR . 'templates/section-taxonomy-grid.php';
    include CDB_PP_DIR . 'templates/section-filter.php';
    return ob_get_clean();
  }

  public static function shortcode_pickup( $atts = [] ) {
    $atts = shortcode_atts([ 'limit' => (int) get_option( 'cdb_pp_pickup_limit', 6 ) ], $atts );
    $q = new WP_Query([
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => (int) $atts['limit'],
      'orderby' => 'rand',
      'date_query' => [[ 'after' => date( 'Y-m-d', strtotime('-2 months') ), 'inclusive' => true ]],
    ]);
    ob_start(); self::loop_cards( $q ); wp_reset_postdata(); return ob_get_clean();
  }

  public static function shortcode_new( $atts = [] ) {
    $atts = shortcode_atts([ 'limit' => (int) get_option( 'cdb_pp_new_limit', 12 ) ], $atts );
    $q = new WP_Query([
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => (int) $atts['limit'],
      'orderby' => 'date',
      'order' => 'DESC',
    ]);
    ob_start();
    echo '<div class="cdb-pp-new" data-per-page="' . esc_attr( (int) $atts['limit'] ) . '">';
    self::loop_cards( $q );
    echo '<button class="cdb-pp-more" type="button">' . esc_html__( 'もっと見る', 'cdb-press-portal' ) . '</button>';
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
  }

  public static function rest_new( WP_REST_Request $req ) {
    $page = max( 1, (int) $req->get_param('page') );
    $per  = max( 1, (int) $req->get_param('per_page') );
    $q = new WP_Query([
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => $per,
      'orderby' => 'date',
      'order' => 'DESC',
      'paged' => $page,
    ]);
    ob_start(); self::loop_cards( $q ); $html = ob_get_clean();
    return new WP_REST_Response([
      'html' => $html,
      'found_posts' => (int) $q->found_posts,
      'max_num_pages' => (int) $q->max_num_pages,
    ], 200 );
  }

  public static function shortcode_taxonomy_list( $atts = [] ) {
    $atts = shortcode_atts([ 'taxonomy' => 'category', 'limit' => 24 ], $atts );
    $terms = get_terms([
      'taxonomy' => sanitize_text_field( $atts['taxonomy'] ),
      'hide_empty' => true,
      'number' => (int) $atts['limit'],
    ]);
    if ( is_wp_error( $terms ) || empty( $terms ) ) return '';
    ob_start();
    echo '<div class="cdb-pp-tax-grid">';
    foreach ( $terms as $term ) {
      $link = get_term_link( $term );
      echo '<a class="cdb-pp-tax-item" href="' . esc_url( $link ) . '">';
      echo '<span class="cdb-pp-tax-name">' . esc_html( $term->name ) . '</span>';
      echo '<span class="cdb-pp-tax-count">' . intval( $term->count ) . '</span>';
      echo '</a>';
    }
    echo '</div>';
    return ob_get_clean();
  }

  public static function loop_cards( WP_Query $q ) {
    if ( ! $q->have_posts() ) { echo '<p class="cdb-pp-empty">' . esc_html__( '該当する記事がありません。', 'cdb-press-portal' ) . '</p>'; return; }
    echo '<div class="cdb-pp-grid">';
    while ( $q->have_posts() ) { $q->the_post();
      $thumb = get_the_post_thumbnail( get_the_ID(), 'medium_large', [ 'class' => 'cdb-pp-thumb' ] );
      $cat   = get_the_category(); $cat_b = $cat ? esc_html( $cat[0]->name ) : '';
      $company = apply_filters( 'cdb_pp_resolve_company_name', '', get_the_ID() );
      echo '<article class="cdb-pp-card" data-post-id="'.esc_attr(get_the_ID()).'">';
      if ( $thumb ) { echo '<div class="cdb-pp-thumbwrap">' . $thumb . '</div>'; }
      echo '<div class="cdb-pp-meta">';
      if ( $cat_b ) echo '<span class="cdb-pp-badge">'.$cat_b.'</span>';
      if ( $company ) echo '<span class="cdb-pp-company">'.esc_html($company).'</span>';
      echo '<time class="cdb-pp-time" datetime="'.esc_attr(get_the_date(DATE_W3C)).'">'.esc_html(get_the_date('Y.m.d')).'</time>';
      echo '</div>';
      echo '<h3 class="cdb-pp-title"><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3>';
      echo '<div class="cdb-pp-share">';
      echo '<button class="cdb-pp-share-btn" data-network="x">Xでシェア</button>';
      echo '<button class="cdb-pp-share-btn" data-network="facebook">Facebook</button>';
      echo '<button class="cdb-pp-share-btn" data-network="line">LINE</button>';
      echo '</div>';
      do_action( 'cdb_pp_render_news_jsonld_inline', get_the_ID() );
      echo '</article>';
    }
    echo '</div>';
  }
}
