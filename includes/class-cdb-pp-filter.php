<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Filter {
  public static function init() {}

  public static function shortcode_filter( $atts = [] ) {
    $cats = get_categories([ 'hide_empty'=>true ]);
    $tags = get_tags([ 'hide_empty'=>true ]);
    ob_start(); ?>
    <section class="cdb-pp-section">
      <header class="cdb-pp-section-hd"><h3>タグクラウド & AJAXフィルタ</h3></header>
      <div class="cdb-pp-filter">
        <select class="cdb-pp-filter-cat">
          <option value="">すべてのカテゴリ</option>
          <?php foreach ( $cats as $c ): ?>
            <option value="<?php echo esc_attr($c->slug); ?>"><?php echo esc_html($c->name); ?></option>
          <?php endforeach; ?>
        </select>
        <select class="cdb-pp-filter-tag">
          <option value="">すべてのタグ</option>
          <?php foreach ( $tags as $t ): ?>
            <option value="<?php echo esc_attr($t->slug); ?>"><?php echo esc_html($t->name); ?></option>
          <?php endforeach; ?>
        </select>
        <input type="search" class="cdb-pp-filter-s" placeholder="キーワード検索" />
        <button class="cdb-pp-filter-do" type="button">絞り込む</button>
      </div>
      <div class="cdb-pp-filter-results"></div>
    </section>
    <?php
    return ob_get_clean();
  }
}
