(function ($) {
  $(function () {
    var frame;
    $('#voidairo-pick-hero').on('click', function (e) {
      e.preventDefault();
      if (frame) { frame.open(); return; }
      frame = wp.media({ title: '选择首页顶部大图', button: { text: '使用这张图片' }, multiple: false });
      frame.on('select', function () {
        var a = frame.state().get('selection').first().toJSON();
        $('#voidairo_hero_image').val(a.url);
        $('#voidairo_hero_image_id').val(a.id);
        $('#voidairo-hero-preview').html('<img src="' + a.url + '" style="max-width:100%;height:auto;border-radius:8px">');
      });
      frame.open();
    });
    $('#voidairo-clear-hero').on('click', function (e) {
      e.preventDefault();
      $('#voidairo_hero_image,#voidairo_hero_image_id').val('');
      $('#voidairo-hero-preview').empty();
    });

    var $sorter = $('#voidairo-meta-sorter');
    function refreshMetaInputs() {
      $sorter.find('.voidairo-meta-item').each(function (index) {
        var $item = $(this);
        var enabled = $item.find('.voidairo-meta-enabled').prop('checked');
        $item.toggleClass('is-disabled', !enabled);
        $item.find('input[type="hidden"]').prop('disabled', !enabled);
        $item.find('.voidairo-meta-position').text(index + 1);
      });
    }
    function moveItem($item, dir) {
      if (dir < 0) $item.prev('.voidairo-meta-item').before($item);
      else $item.next('.voidairo-meta-item').after($item);
      refreshMetaInputs();
    }
    $sorter.on('change', '.voidairo-meta-enabled', refreshMetaInputs);
    $sorter.on('click', '.voidairo-meta-move-up', function (e) { e.preventDefault(); moveItem($(this).closest('.voidairo-meta-item'), -1); });
    $sorter.on('click', '.voidairo-meta-move-down', function (e) { e.preventDefault(); moveItem($(this).closest('.voidairo-meta-item'), 1); });
    $sorter.on('dragstart', '.voidairo-meta-item', function (e) {
      e.originalEvent.dataTransfer.setData('text/plain', $(this).data('key'));
      $(this).addClass('is-dragging');
    });
    $sorter.on('dragend', '.voidairo-meta-item', function () { $(this).removeClass('is-dragging'); refreshMetaInputs(); });
    $sorter.on('dragover', '.voidairo-meta-item', function (e) { e.preventDefault(); });
    $sorter.on('drop', '.voidairo-meta-item', function (e) {
      e.preventDefault();
      var key = e.originalEvent.dataTransfer.getData('text/plain');
      var $drag = $sorter.find('.voidairo-meta-item[data-key="' + key + '"]');
      var $target = $(this);
      if (!$drag.length || $drag[0] === $target[0]) return;
      if ($drag.index() < $target.index()) $target.after($drag); else $target.before($drag);
      refreshMetaInputs();
    });
    refreshMetaInputs();
  });
})(jQuery);
