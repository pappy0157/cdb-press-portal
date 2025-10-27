<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Admin {
  public static function init() {
    add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
    add_action( 'admin_init', [ __CLASS__, 'settings' ] );
  }

  public static function menu() {
    add_options_page( 'Press Portal 設定', 'Press Portal 設定', 'manage_options', 'cdb-pp-settings', [ __CLASS__, 'render' ] );
  }

  public static function settings() {
    register_setting( 'cdb_pp', 'cdb_pp_pickup_limit', ['type'=>'integer','default'=>6] );
    register_setting( 'cdb_pp', 'cdb_pp_new_limit', ['type'=>'integer','default'=>12] );
    register_setting( 'cdb_pp', 'cdb_pp_page_slug', ['type'=>'string','default'=>'pressportal'] );
    register_setting( 'cdb_pp', 'cdb_pp_enable_hatena', ['type'=>'boolean','default'=>0] );
    register_setting( 'cdb_pp', 'cdb_pp_company_post_type', ['type'=>'string','default'=>'company'] );
    register_setting( 'cdb_pp', 'cdb_pp_company_meta_key', ['type'=>'string','default'=>'_cdb_company_id'] );

    add_settings_section( 'cdb_pp_main', '基本設定', '__return_false', 'cdb-pp-settings' );

    add_settings_field( 'pickup_limit', 'ピックアップ件数', [ __CLASS__, 'field_number' ], 'cdb-pp-settings', 'cdb_pp_main', [ 'name'=>'cdb_pp_pickup_limit', 'min'=>1, 'max'=>48 ] );
    add_settings_field( 'new_limit', '新着件数', [ __CLASS__, 'field_number' ], 'cdb-pp-settings', 'cdb_pp_main', [ 'name'=>'cdb_pp_new_limit', 'min'=>3, 'max'=>60 ] );
    add_settings_field( 'page_slug', '特設ページのスラッグ', [ __CLASS__, 'field_text' ], 'cdb-pp-settings', 'cdb_pp_main', [ 'name'=>'cdb_pp_page_slug' ] );

    add_settings_section( 'cdb_pp_social', 'SNS集計', '__return_false', 'cdb-pp-settings' );
    add_settings_field( 'enable_hatena', 'はてなブックマーク取得を試験的に有効化', [ __CLASS__, 'field_checkbox' ], 'cdb-pp-settings', 'cdb_pp_social', [ 'name'=>'cdb_pp_enable_hatena' ] );

    add_settings_section( 'cdb_pp_company', '会社連携', '__return_false', 'cdb-pp-settings' );
    add_settings_field( 'company_pt', '会社 post_type', [ __CLASS__, 'field_text' ], 'cdb-pp-settings', 'cdb_pp_company', [ 'name'=>'cdb_pp_company_post_type' ] );
    add_settings_field( 'company_meta', '記事→会社ID 紐づけmetaキー', [ __CLASS__, 'field_text' ], 'cdb-pp-settings', 'cdb_pp_company', [ 'name'=>'cdb_pp_company_meta_key' ] );
  }

  public static function field_number( $args ) {
    $name = esc_attr($args['name']); $val = (int) get_option($name);
    $min  = isset($args['min'])?(int)$args['min']:1; $max = isset($args['max'])?(int)$args['max']:100;
    echo '<input type="number" min="'.$min.'" max="'.$max.'" name="'.$name.'" value="'.$val.'" class="small-text" />';
  }
  public static function field_text( $args ) {
    $name = esc_attr($args['name']); $val = esc_attr( get_option($name) );
    echo '<input type="text" name="'.$name.'" value="'.$val.'" class="regular-text" />';
  }
  public static function field_checkbox( $args ) {
    $name = esc_attr($args['name']); $val = (int) get_option($name);
    echo '<label><input type="checkbox" name="'.$name.'" value="1" '.checked(1,$val,false).' /> 有効化</label>';
  }

  public static function render() {
    ?>
    <div class="wrap">
      <h1>Press Portal 設定</h1>
      <form method="post" action="options.php">
        <?php settings_fields('cdb_pp'); do_settings_sections('cdb-pp-settings'); submit_button(); ?>
      </form>
      <hr />
      <p>スラッグ変更後は「パーマリンク設定」を再保存してください。</p>
    </div>
    <?php
  }
}
