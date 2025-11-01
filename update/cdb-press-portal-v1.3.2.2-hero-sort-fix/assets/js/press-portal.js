(function($){
  $(document).on('click', '.cdb-pp-more', function(){
    var $wrap = $(this).closest('.cdb-pp-new');
    var per   = parseInt($wrap.data('per-page')||12,10);
    var page  = parseInt($wrap.data('page')||1,10) + 1;
    var $btn  = $(this);
    $btn.prop('disabled', true).text(CDBPP.labels.loading);
    $.ajax({
      url: CDBPP.rest.root + '/new',
      method: 'GET',
      data: { page: page, per_page: per },
      headers: { 'X-WP-Nonce': CDBPP.rest.nonce }
    }).done(function(res){
      if(res && res.html){
        $wrap.find('.cdb-pp-grid').last().after(res.html);
        $wrap.data('page', page);
        if(page >= (res.max_num_pages||1)){ $btn.remove(); }
        else { $btn.prop('disabled', false).text(CDBPP.labels.more); }
      } else { $btn.remove(); }
    }).fail(function(){ $btn.prop('disabled', false).text(CDBPP.labels.more); });
  });
})(jQuery);

(function($){
  // Filter: "絞り込む" button
  $(document).on('click', '.cdb-pp-filter-do', function(){
    var $section = $(this).closest('.cdb-pp-section');
    var cat = $.trim($section.find('.cdb-pp-filter-cat').val() || '');
    var s   = $.trim($section.find('.cdb-pp-filter-s').val() || '');
    var tag = $.trim($section.find('.cdb-pp-filter-tag').val() || '');
    // Find nearest results container
    var $target = $section.nextAll('.cdb-pp-new').first().find('.cdb-pp-grid').first();
    if(!$target.length){ $target = $section.nextAll('.cdb-pp-grid').first(); }
    if(!$target.length){ $target = $('.cdb-pp-grid').first(); }
    $target.addClass('cdb-pp-filter-results').addClass('is-loading');
    $.ajax({
      url: CDBPP.rest.root + '/filter',
      method: 'GET',
      data: { category: cat, s: s, tag: tag, page: 1 },
      headers: { 'X-WP-Nonce': CDBPP.rest.nonce }
    }).done(function(res){
      if(res && res.html){ $target.html(res.html); }
    }).always(function(){
      $target.removeClass('is-loading');
    });
  });

  // Share buttons: X, Facebook, LINE
  function openShare(network, url, text){
    var shareUrl = '';
    if(network === 'x'){
      shareUrl = 'https://x.com/intent/tweet?url=' + encodeURIComponent(url) + (text ? '&text=' + encodeURIComponent(text) : '');
    } else if(network === 'facebook'){
      shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
    } else if(network === 'line'){
      shareUrl = 'https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(url);
    }
    if(shareUrl){ window.open(shareUrl, '_blank', 'noopener,noreferrer,width=680,height=520'); }
  }

  $(document).on('click', '.cdb-pp-share-btn', function(e){
    e.preventDefault();
    var $btn = $(this);
    var network = ($btn.data('network')||'').toString().toLowerCase();
    // Try to get post/link info
    var postId = parseInt($btn.data('postId') || $btn.attr('data-post-id') || 0, 10);
    var $card = $btn.closest('.cdb-pp-card');
    var url = $btn.data('url') || ($card.find('.cdb-pp-title a').attr('href')) || window.location.href;
    var text = $card.find('.cdb-pp-title a').text() || document.title;
    // Increment social score (non-blocking)
    if(postId){
      $.ajax({
        url: CDBPP.rest.root + '/social-hit',
        method: 'POST',
        data: { post_id: postId },
        headers: { 'X-WP-Nonce': CDBPP.rest.nonce }
      });
    }
    openShare(network, url, text);
  });
})(jQuery);


(function($){
  // --- Hero Slider ---
  function initHero($hero){
    var $track = $hero.find('.cdb-pp-hero-track');
    var $slides = $track.find('.cdb-pp-hero-slide');
    if(!$slides.length) return;
    var idx = 0, timer = null;
    var autoplay = parseInt($hero.data('autoplay')||1,10) === 1;
    var interval = parseInt($hero.data('interval')||5000,10);

    // Build dots
    var $dotsWrap = $hero.find('.cdb-pp-slider-dots').empty();
    $slides.each(function(i){
      var $b = $('<button>',{'type':'button','aria-label':'slide '+(i+1)});
      $b.on('click', function(){ go(i); restart(); });
      $dotsWrap.append($b);
    });
    function mark(){
      $dotsWrap.find('button').removeClass('is-active').eq(idx).addClass('is-active');
    }
    function go(i){
      idx = (i + $slides.length) % $slides.length;
      var x = -idx * 100;
      $track.css('transform','translateX(' + x + '%)');
      mark();
    }
    function next(){ go(idx+1); }
    function prev(){ go(idx-1); }
    $hero.find('.cdb-pp-slider-nav.next').off('click').on('click', function(e){ e.preventDefault(); next(); restart(); });
    $hero.find('.cdb-pp-slider-nav.prev').off('click').on('click', function(e){ e.preventDefault(); prev(); restart(); });
    function start(){ if(autoplay){ timer = setInterval(next, interval); } }
    function stop(){ if(timer){ clearInterval(timer); timer=null; } }
    function restart(){ stop(); start(); }
    $hero.on('mouseenter focusin', stop).on('mouseleave focusout', start);
    go(0); start();
  }

  $(function(){
    $('.cdb-pp-hero.slider').each(function(){ initHero($(this)); });
  });
})(jQuery);

(function($){
  // --- Reorder sections on the portal page to the requested sequence ---
  // Target sequence: Filter, New, Pickup, Social Hot, Today Rank, Month Rank
  $(function(){
    var $root = $('.cdb-pp-portal, [data-cdb-portal-root]').first();
    var $body = $root.length ? $root : $(document.body);
    function findSection(sel){ return $body.find(sel).first().closest('.cdb-pp-section'); }

    var $filter = findSection('.cdb-pp-filter');
    var $new    = $body.find('.cdb-pp-new').first().closest('.cdb-pp-section, .cdb-pp-new');
    var $pickup = $body.find('.cdb-pp-pickup').first().closest('.cdb-pp-section, .cdb-pp-pickup');
    var $social = $body.find('.cdb-pp-social').first().closest('.cdb-pp-section, .cdb-pp-social');
    var $today  = $body.find('.cdb-pp-today-rank').first().closest('.cdb-pp-section, .cdb-pp-today-rank');
    var $month  = $body.find('.cdb-pp-month-rank').first().closest('.cdb-pp-section, .cdb-pp-month-rank');

    // Choose anchor: put after hero if present
    var $anchor = $body.find('.cdb-pp-hero').last();
    if(!$anchor.length){ $anchor = $body.find('.cdb-pp-section').first(); }

    var list = [$filter, $new, $pickup, $social, $today, $month].filter(function($e){ return $e && $e.length; });
    list.forEach(function($e){ $e.insertAfter($anchor); $anchor = $e; });
  });
})(jQuery);

