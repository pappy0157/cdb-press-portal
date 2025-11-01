<?php
/**
 * Plugin Name: CDB Press Portal
 * Description: プレスリリース特設ページ（/pressportal）を自動生成。ピックアップ・新着・ランキング・SNSで話題・会社連携・タグクラウドAJAXフィルタ・構造化データを提供。ヒーローはスライダー対応。
 * Version: 1.2.1
 * Author: CDB Team
 * License: GPL-2.0+
 * Text Domain: cdb-press-portal
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'CDB_PP_VERSION', '1.2.1' );
define( 'CDB_PP_FILE', __FILE__ );
define( 'CDB_PP_DIR', plugin_dir_path( __FILE__ ) );
define( 'CDB_PP_URL', plugin_dir_url( __FILE__ ) );

require_once CDB_PP_DIR . 'includes/class-cdb-pp-loader.php';
require_once CDB_PP_DIR . 'includes/class-cdb-pp-admin.php';
require_once CDB_PP_DIR . 'includes/class-cdb-pp-ranking.php';
require_once CDB_PP_DIR . 'includes/class-cdb-pp-social.php';
require_once CDB_PP_DIR . 'includes/class-cdb-pp-filter.php';
require_once CDB_PP_DIR . 'includes/class-cdb-pp-schema.php';
require_once CDB_PP_DIR . 'includes/class-cdb-pp-company.php';

register_activation_hook( __FILE__, function() {
  add_option( 'cdb_pp_pickup_limit', 6 );
  add_option( 'cdb_pp_new_limit', 12 );
  add_option( 'cdb_pp_page_slug', 'pressportal' );
  add_option( 'cdb_pp_enable_hatena', 0 );
  add_option( 'cdb_pp_company_post_type', 'company' );
  add_option( 'cdb_pp_company_meta_key', '_cdb_company_id' );

  $slug = get_option( 'cdb_pp_page_slug', 'pressportal' );
  $page = get_page_by_path( $slug );
  if ( ! $page ) {
    $page_id = wp_insert_post([
      'post_title'   => 'プレスリリース特設ページ',
      'post_name'    => sanitize_title( $slug ),
      'post_status'  => 'publish',
      'post_type'    => 'page',
      'post_content' => '[cdb_press_portal]'
    ]);
    if ( ! is_wp_error( $page_id ) ) { update_option( 'cdb_pp_page_id', $page_id ); }
  }

  if ( ! wp_next_scheduled( 'cdb_pp_cron_decay_social_score' ) ) {
    wp_schedule_event( time() + 3600, 'hourly', 'cdb_pp_cron_decay_social_score' );
  }
});

register_deactivation_hook( __FILE__, function(){
  wp_clear_scheduled_hook( 'cdb_pp_cron_decay_social_score' );
});

add_action( 'plugins_loaded', function() {
  if ( class_exists('CDB_PP_Loader') ) CDB_PP_Loader::init();
  if ( class_exists('CDB_PP_Admin') ) CDB_PP_Admin::init();
  if ( class_exists('CDB_PP_Ranking') ) CDB_PP_Ranking::init();
  if ( class_exists('CDB_PP_Social') ) CDB_PP_Social::init();
  if ( class_exists('CDB_PP_Filter') ) CDB_PP_Filter::init();
  if ( class_exists('CDB_PP_Schema') ) CDB_PP_Schema::init();
  if ( class_exists('CDB_PP_Company') ) CDB_PP_Company::init();
});

add_action( 'wp_enqueue_scripts', function() {
  wp_enqueue_style( 'cdb-pp-style', CDB_PP_URL . 'assets/css/press-portal.css', [], CDB_PP_VERSION );
  wp_enqueue_script( 'cdb-pp-script', CDB_PP_URL . 'assets/js/press-portal.js', ['jquery'], CDB_PP_VERSION, true );
  wp_localize_script( 'cdb-pp-script', 'CDBPP', [
    'rest' => [ 'root'  => esc_url_raw( rest_url( 'cdb/v1' ) ), 'nonce' => wp_create_nonce( 'wp_rest' ) ],
    'labels' => [ 'more' => __('もっと見る','cdb-press-portal'), 'loading' => __('読み込み中…','cdb-press-portal'),
                  'prev'=>__('前へ','cdb-press-portal'),'next'=>__('次へ','cdb-press-portal') ]
  ]);
});
