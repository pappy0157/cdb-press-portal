(function($){
  // Load more for New
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

  // Share buttons -> open window + ping REST to increment social score
  $(document).on('click', '.cdb-pp-share-btn', function(){
    var $card = $(this).closest('.cdb-pp-card');
    var postId = parseInt($card.data('post-id'), 10);
    var url = $card.find('.cdb-pp-title a').attr('href');
    var network = $(this).data('network');
    var shareUrl = url;
    if (network === 'x') shareUrl = 'https://x.com/intent/tweet?url=' + encodeURIComponent(url);
    if (network === 'facebook') shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
    if (network === 'line') shareUrl = 'https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(url);
    window.open(shareUrl, '_blank', 'width=680,height=560');

    $.ajax({
      url: CDBPP.rest.root + '/social-hit',
      method: 'POST',
      data: { post_id: postId },
      headers: { 'X-WP-Nonce': CDBPP.rest.nonce }
    });
  });

  // AJAX Filter
  function runFilter(page){
    var cat = $('.cdb-pp-filter-cat').val()||'';
    var tag = $('.cdb-pp-filter-tag').val()||'';
    var s   = $('.cdb-pp-filter-s').val()||'';
    var $res= $('.cdb-pp-filter-results');
    if (!page) page = 1;
    $res.addClass('is-loading');
    $.ajax({
      url: CDBPP.rest.root + '/filter',
      method: 'GET',
      data: { category: cat, tag: tag, s: s, page: page },
      headers: { 'X-WP-Nonce': CDBPP.rest.nonce }
    }).done(function(res){
      $res.removeClass('is-loading').html(res.html);
      if ((res.max_num_pages||1) > page){
        $res.append('<button class="cdb-pp-filter-more" data-page="'+(page+1)+'">'+CDBPP.labels.more+'</button>');
      }
    }).fail(function(){ $res.removeClass('is-loading').html('<p>読み込みに失敗しました。</p>'); });
  }

  $(document).on('click', '.cdb-pp-filter-do', function(){ runFilter(1); });
  $(document).on('click', '.cdb-pp-filter-more', function(){
    var next = parseInt($(this).data('page'),10);
    $(this).remove();
    runFilter(next);
  });
})(jQuery);
