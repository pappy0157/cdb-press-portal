<?php
$hero_args = [ 'post_type'=>'post', 'post_status'=>'publish', 'posts_per_page'=>5, 'ignore_sticky_posts'=>true ];
$featured = get_category_by_slug( 'featured' );
if ( $featured ) $hero_args['cat'] = (int)$featured->term_id;
$hero_q = new WP_Query( $hero_args );
if ( $hero_q->have_posts() ) : ?>
<section class="cdb-pp-hero slider" data-autoplay="1" data-interval="5000">
  <div class="cdb-pp-hero-track">
    <?php while ( $hero_q->have_posts() ) : $hero_q->the_post(); ?>
      <div class="cdb-pp-hero-slide">
        <div class="cdb-pp-hero-inner">
          <div class="cdb-pp-hero-media"><?php if ( has_post_thumbnail() ) the_post_thumbnail('large'); ?></div>
          <div class="cdb-pp-hero-body">
            <span class="cdb-pp-badge cdb-pp-badge--accent"><?php $c=get_the_category(); echo $c?esc_html($c[0]->name):''; ?></span>
            <h2 class="cdb-pp-hero-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p class="cdb-pp-hero-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
            <a class="cdb-pp-cta" href="<?php the_permalink(); ?>"><?php esc_html_e('詳しく見る','cdb-press-portal'); ?></a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
  <button class="cdb-pp-slider-nav prev" type="button" aria-label="prev">&larr; <?php esc_html_e('前へ','cdb-press-portal'); ?></button>
  <button class="cdb-pp-slider-nav next" type="button" aria-label="next"><?php esc_html_e('次へ','cdb-press-portal'); ?> &rarr;</button>
  <div class="cdb-pp-slider-dots"></div>
</section>
<?php endif; wp_reset_postdata(); ?>
