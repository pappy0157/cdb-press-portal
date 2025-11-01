<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class CDB_PP_Company {
  public static function init() {
    add_action( 'add_meta_boxes', [ __CLASS__, 'add_box' ] );
    add_action( 'save_post', [ __CLASS__, 'save' ] );
    add_action( 'init', [ __CLASS__, 'register_meta_for_rest' ] );
    add_action( 'wp_ajax_cdb_pp_company_search', [ __CLASS__, 'ajax_search' ] );
  }
  public static function add_box( $post_type ) {
    if ( 'post' !== $post_type ) return;
    add_meta_box( 'cdb_pp_company_box', '会社を選択', [ __CLASS__, 'render_box' ], 'post', 'side', 'default' );
  }
  public static function render_box( $post ) {
    $meta_key   = get_option('cdb_pp_company_meta_key','_cdb_company_id');
    $pt_company = get_option('cdb_pp_company_post_type','company');
    $company_id = (int) get_post_meta( $post->ID, $meta_key, true );
    wp_nonce_field( 'cdb_pp_company_box', 'cdb_pp_company_box_nonce' );
    echo '<p><label for="cdb_pp_company_search">会社を検索</label>';
    echo '<input type="text" id="cdb_pp_company_search" class="widefat" placeholder="社名の一部..." oninput="cdbPPcompanySearch(this.value)" /></p>';
    echo '<select id="cdb_pp_company_id" name="cdb_pp_company_id" class="widefat">';
    echo '<option value="">— 未選択 —</option>';
    $companies = get_posts([ 'post_type'=>$pt_company, 'post_status'=>'publish', 'posts_per_page'=>50, 'orderby'=>'date', 'order'=>'DESC', 'fields'=>'ids' ]);
    foreach ( $companies as $cid ) { $sel = selected( $company_id, $cid, false ); echo '<option value="'.esc_attr($cid).'" '.$sel.'>'.esc_html(get_the_title($cid)).' (ID:'.$cid.')</option>'; }
    echo '</select>';
    ?> <script>function cdbPPcompanySearch(q){if(!q||q.length<2){return;}jQuery.get(ajaxurl,{action:'cdb_pp_company_search',q:q},function(html){if(html){jQuery('#cdb_pp_company_id').html(html);}});}</script> <?php
  }
  public static function ajax_search() {
    if ( ! current_user_can('edit_posts') ) wp_die();
    $q  = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    $pt = get_option('cdb_pp_company_post_type','company');
    $res = get_posts([ 'post_type'=>$pt, 'post_status'=>'publish', 's'=>$q, 'posts_per_page'=>50, 'fields'=>'ids' ]);
    echo '<option value="">— 未選択 —</option>';
    foreach ( $res as $cid ) { echo '<option value="'.esc_attr($cid).'">'.esc_html(get_the_title($cid)).' (ID:'.$cid.')</option>'; }
    wp_die();
  }
  public static function save( $post_id ) {
    if ( ! isset($_POST['cdb_pp_company_box_nonce']) || ! wp_verify_nonce( $_POST['cdb_pp_company_box_nonce'], 'cdb_pp_company_box' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $meta_key   = get_option('cdb_pp_company_meta_key','_cdb_company_id');
    $pt_company = get_option('cdb_pp_company_post_type','company');
    $raw = isset($_POST['cdb_pp_company_id']) ? trim($_POST['cdb_pp_company_id']) : '';
    if ( $raw === '' ) { delete_post_meta( $post_id, $meta_key ); return; }
    $company_id = (int) $raw;
    $p = get_post( $company_id );
    if ( $p && $p->post_type === $pt_company ) { update_post_meta( $post_id, $meta_key, $company_id ); }
  }
  public static function register_meta_for_rest() {
    $meta_key = get_option('cdb_pp_company_meta_key','_cdb_company_id');
    register_post_meta( 'post', $meta_key, [
      'type' => 'integer', 'single' => true, 'show_in_rest' => true,
      'sanitize_callback' => 'absint',
      'auth_callback' => function(){ return current_user_can('edit_posts'); },
    ]);
  }
}