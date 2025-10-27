<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Company {
  public static function init() {
    add_filter( 'cdb_pp_resolve_company_name', [ __CLASS__, 'resolve_company_name' ], 10, 2 );
    add_shortcode( 'cdb_press_companies', [ __CLASS__, 'shortcode_company_block' ] );
  }

  // Try to resolve company linked to a post via meta or taxonomy
  public static function resolve_company_name( $name, $post_id ) {
    $meta_key = get_option('cdb_pp_company_meta_key', '_cdb_company_id');
    $pt       = get_option('cdb_pp_company_post_type', 'company');
    $company_id = (int) get_post_meta( $post_id, $meta_key, true );
    if ( $company_id ) {
      $post = get_post( $company_id );
      if ( $post && $post->post_type === $pt ) return get_the_title( $post );
    }
    // fallback: taxonomy 'company' if exists
    if ( taxonomy_exists('company') ) {
      $terms = get_the_terms( $post_id, 'company' );
      if ( $terms && ! is_wp_error($terms) ) return $terms[0]->name;
    }
    return $name;
  }

  public static function shortcode_company_block( $atts = [] ) {
    $atts = shortcode_atts([ 'limit'=>8 ], $atts );
    $pt   = get_option('cdb_pp_company_post_type', 'company');
    if ( ! post_type_exists( $pt ) ) return '';
    // Companies with most related posts (join by meta)
    global $wpdb;
    $meta_key = esc_sql( get_option('cdb_pp_company_meta_key', '_cdb_company_id') );
    $sql = $wpdb->prepare(      "SELECT m.meta_value as company_id, COUNT(p.ID) as cnt
       FROM {$wpdb->postmeta} m
       JOIN {$wpdb->posts} p ON p.ID = m.post_id AND p.post_status='publish' AND p.post_type='post'
       WHERE m.meta_key=%s AND m.meta_value<>''
       GROUP BY m.meta_value
       ORDER BY cnt DESC
       LIMIT %d", $meta_key, (int)$atts['limit']
    );
    $rows = $wpdb->get_results( $sql );
    if ( empty($rows) ) return '';
    ob_start();
    echo '<div class="cdb-pp-company-grid">';
    foreach( $rows as $r ){
      $cid = (int)$r->company_id;
      $c   = get_post( $cid );
      if ( ! $c || $c->post_type !== $pt ) continue;
      $logo = get_the_post_thumbnail( $cid, 'thumbnail', [ 'class'=>'cdb-pp-company-logo' ] );
      echo '<a class="cdb-pp-company-card" href="'.esc_url(get_permalink($cid)).'">';
      if ( $logo ) echo '<div class="cdb-pp-company-logo-wrap">'.$logo.'</div>';
      echo '<div class="cdb-pp-company-name">'.esc_html(get_the_title($cid)).'</div>';
      echo '<div class="cdb-pp-company-count">'.intval($r->cnt).'件のプレス</div>';
      echo '</a>';
    }
    echo '</div>';
    return ob_get_clean();
  }
}
