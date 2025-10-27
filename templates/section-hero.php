<?php
$hero_args = [ 'post_type'=>'post', 'post_status'=>'publish', 'posts_per_page'=>1, 'ignore_sticky_posts'=>true ];
$featured = get_category_by_slug( 'featured' );
if ( $featured ) $hero_args['cat'] = (int)$featured->term_id;
$hero_q = new WP_Query( $hero_args );
if ( $hero_q->have_posts() ) : $hero_q->the_post(); ?>
<section class="cdb-pp-hero">
  <div class="cdb-pp-hero-inner">
    <div class="cdb-pp-hero-media"><?php if ( has_post_thumbnail() ) the_post_thumbnail('large'); ?></div>
    <div class="cdb-pp-hero-body">
      <span class="cdb-pp-badge cdb-pp-badge--accent"><?php $c=get_the_category(); echo $c?esc_html($c[0]->name):''; ?></span>
      <h2 class="cdb-pp-hero-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <p class="cdb-pp-hero-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
      <a class="cdb-pp-cta" href="<?php the_permalink(); ?>"><?php esc_html_e('詳しく見る','cdb-press-portal'); ?></a>
    </div>
  </div>
</section>
<?php endif; wp_reset_postdata(); ?>
