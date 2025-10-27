<?php $pickup_limit = (int)get_option('cdb_pp_pickup_limit',6); ?>
<section class="cdb-pp-section">
  <header class="cdb-pp-section-hd"><h3>ピックアップ</h3></header>
  <div class="cdb-pp-section-bd">
    <?php echo do_shortcode('[cdb_press_pickup limit="'.$pickup_limit.'"]'); ?>
  </div>
</section>
