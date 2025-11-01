<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Filter {
  public static function init() {
    add_shortcode( 'cdb_press_filter', [ __CLASS__, 'shortcode' ] );
  }

  public static function shortcode( $atts = [] ) {
    $cats = get_categories([ 'hide_empty'=>true ]);
    ob_start();
    echo '<section class="cdb-pp-section"><div class="cdb-pp-section-hd"><h3>記事を絞り込む</h3></div>';
    echo '<div class="cdb-pp-filter">';
    echo '<select class="cdb-pp-filter-cat"><option value="">カテゴリ</option>';
    foreach ( $cats as $c ) {
      echo '<option value="'.esc_attr($c->slug).'">'.esc_html($c->name).'</option>';
    }
    echo '</select>';
    echo '<input type="text" class="cdb-pp-filter-s" placeholder="'.esc_attr__('キーワード','cdb-press-portal').'" />';
    echo '<button type="button" class="cdb-pp-filter-do">'.esc_html__('絞り込む','cdb-press-portal').'</button>';
    echo '</div>';

    // タグクラウド（クリックでajax）
    $tags = get_terms([ 'taxonomy'=>'post_tag', 'hide_empty'=>true, 'number'=>40, 'orderby'=>'count', 'order'=>'DESC' ]);
    if ( ! is_wp_error($tags) && $tags ) {
      echo '<div class="cdb-pp-tagcloud">';
      foreach ( $tags as $t ) {
        echo '<button class="cdb-pp-tag" data-tag="'.esc_attr($t->slug).'">#'.esc_html($t->name).'</button> ';
      }
      echo '</div>';
    }
    echo '<div class="cdb-pp-filter-results"></div>';
    echo '</section>';

    // JS: tag click -> run filter
    ?>
    <script>
      (function($){
        $(document).on('click','.cdb-pp-tag',function(){
          var slug = $(this).data('tag')||'';
          $('.cdb-pp-filter-tag').remove(); // hidden field (ensure single)
          $('<input>',{type:'hidden',class:'cdb-pp-filter-tag',value:slug}).appendTo('.cdb-pp-section .cdb-pp-filter');
          $('.cdb-pp-filter-do').trigger('click');
        });
      })(jQuery);
    </script>
    <?php

    return ob_get_clean();
  }
}
