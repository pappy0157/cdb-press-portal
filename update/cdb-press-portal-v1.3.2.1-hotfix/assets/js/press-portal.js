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
