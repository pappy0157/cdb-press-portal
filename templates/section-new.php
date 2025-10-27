<?php $new_limit = (int)get_option('cdb_pp_new_limit',12); ?>
<section class="cdb-pp-section">
  <header class="cdb-pp-section-hd"><h3>新着プレスリリース</h3></header>
  <div class="cdb-pp-section-bd">
    <?php echo do_shortcode('[cdb_press_new limit="'.$new_limit.'"]'); ?>
  </div>
</section>
