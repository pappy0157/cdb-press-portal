<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CDB_PP_Schema {
  public static function init() {
    add_action( 'cdb_pp_render_news_jsonld_inline', [ __CLASS__, 'render_news_jsonld' ] );
    add_action( 'wp_head', [ __CLASS__, 'portal_page_jsonld' ] );
  }

  // Inline NewsArticle for each card
  public static function render_news_jsonld( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post ) return;
    $logo = get_site_icon_url();
    $img  = get_the_post_thumbnail_url( $post_id, 'large' );
    $data = [
      "@context" => "https://schema.org",
      "@type"    => "NewsArticle",
      "headline" => wp_strip_all_tags( get_the_title($post_id) ),
      "datePublished" => get_post_time( 'c', true, $post ),
      "dateModified"  => get_post_modified_time( 'c', true, $post ),
      "author" => [ "@type"=>"Organization", "name"=> get_bloginfo('name') ],
      "publisher" => [ "@type"=>"Organization", "name"=> get_bloginfo('name'), "logo"=> [ "@type"=>"ImageObject", "url"=>$logo?:'' ] ],
      "mainEntityOfPage" => get_permalink( $post_id ),
    ];
    if ( $img ) $data["image"] = [ $img ];
    echo '<script type="application/ld+json">'.wp_json_encode($data).'</script>';
  }

  // Portal page structured data
  public static function portal_page_jsonld() {
    if ( is_page( get_option('cdb_pp_page_id') ) ) {
      $data = [
        "@context"=>"https://schema.org",
        "@type"=>"CollectionPage",
        "name"=>"プレスリリース特設ページ",
        "url"=> get_permalink( get_option('cdb_pp_page_id') ),
        "isPartOf"=>[ "@type"=>"WebSite", "name"=> get_bloginfo('name'), "url"=> home_url('/') ]
      ];
      echo '<script type="application/ld+json">'.wp_json_encode($data).'</script>';
    }
  }
}
