(function ($) {
  if (!$) return;

  var BODY_CLASS = 'mj10-goods-list';

  function ensureCartLayers() {
    if (!$('#optionViewLayer').length) {
      $('body').append('<div id="optionViewLayer" class="layer_wrap dn"></div>');
    }

    if (!$('#addCartLayer').length) {
      $('body').append(
        '<div id="addCartLayer" class="layer_wrap dn">' +
          '<div class="box add_cart_layer" style="position: absolute; margin: 0px;">' +
            '<div class="view">' +
              '<h2>장바구니 담기</h2>' +
              '<div class="scroll_box">' +
                '<p class="success"><strong>상품이 장바구니에 담겼습니다.</strong><br>바로 확인하시겠습니까?</p>' +
              '</div>' +
              '<div class="btn_box">' +
                '<button type="button" class="btn_cancel"><span>취소</span></button>' +
                '<button type="button" class="btn_confirm btn_move_cart"><span>확인</span></button>' +
              '</div>' +
              '<button title="닫기" class="close" type="button">닫기</button>' +
            '</div>' +
          '</div>' +
        '</div>'
      );
    }
  }

  function resetLayerVisibility() {
    if ($('#optionViewLayer').is(':visible') || $('#addCartLayer').is(':visible')) {
      $('#optionViewLayer, #addCartLayer').addClass('dn');
      $('#layerDim').addClass('dn');
      if ($('body').css('overflow') === 'hidden') {
        $('body').css('overflow', '');
      }
    }
  }

  function autoSelectFirstOptions($root) {
    if ($root.data('auto-option-done')) {
      return false;
    }
    if ($root.find('.option_display_area tr').length) {
      $root.data('auto-option-done', true);
      return false;
    }

    var changed = false;
    $root.find('select[name="optionSnoInput"]').each(function () {
      var $sel = $(this);
      if ($sel.val()) return;
      var $opt = $sel.find('option[value!=""]:not(:disabled)').first();
      if ($opt.length) {
        $sel.val($opt.val()).trigger('change');
        if ($sel.hasClass('chosen-select')) {
          $sel.trigger('chosen:updated');
        }
        changed = true;
      }
    });

    $root.find('select[name^="optionNo_"]').each(function () {
      var $sel = $(this);
      if ($sel.prop('disabled') || $sel.val()) return;
      var $opt = $sel.find('option[value!=""]:not(:disabled)').first();
      if ($opt.length) {
        $sel.val($opt.val()).trigger('change');
        if ($sel.hasClass('chosen-select')) {
          $sel.trigger('chosen:updated');
        }
        changed = true;
      }
    });

    if ($root.find('.option_display_area tr').length) {
      $root.data('auto-option-done', true);
    }

    return changed;
  }

  function updateLayerTexts($root) {
    var goodsName = $.trim($root.find('.option_tit_box dd strong').first().text());
    if (goodsName) {
      $root.find('.option_display_area .cart_tit_box .cart_tit > span').text(goodsName);
    }
    $root.find('h4').text('장바구니');
  }

  function runLayerScripts($scripts) {
    if (!$scripts || !$scripts.length) return;
    var loaded = window.__mj10LoadedScripts || (window.__mj10LoadedScripts = {});
    $scripts.each(function () {
      var type = (this.getAttribute('type') || '').toLowerCase();
      if (type && type !== 'text/javascript' && type !== 'application/javascript') {
        return;
      }
      var src = this.getAttribute('src');
      if (src) {
        if (loaded[src] || document.querySelector('script[src="' + src + '"]')) return;
        loaded[src] = true;
        $.ajax({
          url: src,
          dataType: 'script',
          cache: true,
          async: false
        });
        return;
      }
      var code = this.text || this.textContent || this.innerHTML;
      if (!code) return;
      if ($.globalEval) {
        $.globalEval(code);
      } else {
        (0, eval)(code);
      }
    });
  }

  function renderOptionLayer(html) {
    var $layer = $('#optionViewLayer');
    var $tmp = $('<div />').html(html);
    var $scripts = $tmp.find('script');
    $scripts.remove();
    $layer.empty().append($tmp.contents());
    runLayerScripts($scripts);
    return $layer;
  }

  function bindCartHandlers() {
    if ($(document).data('mj10-main-cart-bound')) return;
    $(document).data('mj10-main-cart-bound', true);

    $(document).on('click', '.js-main-cart', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var goodsNo = $btn.data('goodsNo');
      if (!goodsNo) goodsNo = $btn.data('goods-no');
      if (!goodsNo) goodsNo = $btn.attr('data-goods-no') || $btn.attr('data-goodsno');
      if (!goodsNo) return;

      var params = { page: 'goods', type: 'goods', goodsNo: goodsNo };

      $.ajax({
        method: 'POST',
        cache: false,
        url: '../goods/layer_option.php',
        data: params,
        success: function (data) {
          var $layer = renderOptionLayer(data);
          $layer.data('auto-option-done', false);
          $layer.removeClass('dn');
          $('#layerDim').removeClass('dn');
          $('body').css('overflow', 'hidden');
          $layer.find('>div').center();
          updateLayerTexts($layer);

          var tries = 0;
          (function runAutoSelect() {
            if ($layer.data('auto-option-done') || $layer.find('.option_display_area tr').length) {
              $layer.data('auto-option-done', true);
              updateLayerTexts($layer);
              $layer.find('>div').center();
              return;
            }

            var changed = autoSelectFirstOptions($layer);
            tries += 1;
            if (changed && tries < 6) {
              setTimeout(runAutoSelect, 60);
              return;
            }
            $layer.data('auto-option-done', true);
            updateLayerTexts($layer);
            $layer.find('>div').center();
          })();
        },
        error: function (data) {
          alert((data && data.message) || '장바구니 처리 중 오류가 발생했습니다.');
        }
      });
    });

    $(document).on('click', '.btn_move_cart', function () {
      location.href = '../order/cart.php';
    });

    $(document).on('click', '#addCartLayer .btn_cancel, #addCartLayer .close', function () {
      $('#addCartLayer').addClass('dn');
      if (!$('.layer_wrap').is(':visible')) {
        $('#layerDim').addClass('dn');
        $('body').removeAttr('style');
      }
    });
  }

  function ensureOptionResultHandler() {
    if (typeof window.gd_goods_option_view_result === 'function') return;

    window.gd_goods_option_view_result = function (params) {
      params += '&mode=cartIn';

      $.ajax({
        method: 'POST',
        cache: false,
        url: '../order/cart_ps.php',
        data: params,
        success: function () {
          if (typeof gd_close_layer === 'function') {
            gd_close_layer();
          }
          var info = window.mj10CartInfo || window.cartInfo;
          if (info && (info.moveCartPageFl === 'y' || (info.moveCartPageFl === 'n' && info.moveCartPageDeviceFl === 'mobile'))) {
            location.href = '../order/cart.php';
            return;
          }
          $('#optionViewLayer').addClass('dn');
          $('#addCartLayer').removeClass('dn');
          $('#layerDim').removeClass('dn');
          $('body').css('overflow', 'hidden');
          $('#addCartLayer').find('> div').center();
        },
        error: function (data) {
          alert((data && data.message) || '장바구니 처리 중 오류가 발생했습니다.');
        }
      });
    };
  }

  function setupPickListToggle() {
    var $boxes = $('.goods_pick_list .pick_list_box');
    if (!$boxes.length) return;
    var mq = window.matchMedia('(max-width: 768px)');

    $boxes.each(function () {
      var $box = $(this);
      if ($box.data('mj10-pick-toggle')) return;
      $box.data('mj10-pick-toggle', true);
      var $list = $box.children('.pick_list').first();
      var $toggle = $('<button type="button" class="pick_list_toggle" aria-expanded="false">정렬 옵션</button>');
      $toggle.insertBefore($list);
      $toggle.on('click', function () {
        if (!mq.matches) return;
        var isOpen = $box.toggleClass('is-open').hasClass('is-open');
        if (isOpen) {
          $box.removeClass('is-collapsed');
          $toggle.attr('aria-expanded', 'true');
          $list.stop(true, true).slideDown(150, function () {
            $list.css('display', 'flex');
          });
        } else {
          $box.addClass('is-collapsed');
          $toggle.attr('aria-expanded', 'false');
          $list.stop(true, true).slideUp(150);
        }
      });
    });

    function syncPickListState() {
      $boxes.each(function () {
        var $box = $(this);
        var $list = $box.children('.pick_list').first();
        var $toggle = $box.children('.pick_list_toggle');
        if (mq.matches) {
          if (!$box.hasClass('is-open')) {
            $box.addClass('is-collapsed');
            $toggle.attr('aria-expanded', 'false');
            $list.hide();
          }
        } else {
          $box.removeClass('is-open is-collapsed');
          $toggle.attr('aria-expanded', 'false');
          $list.show();
        }
      });
    }

    if (mq.addEventListener) {
      mq.addEventListener('change', syncPickListState);
    } else if (mq.addListener) {
      mq.addListener(syncPickListState);
    } else {
      $(window).on('resize.pickList', syncPickListState);
    }

    syncPickListState();
  }

  function initGoodsList() {
    if (window.__mj10GoodsListInit) return;
    window.__mj10GoodsListInit = true;
    if (document.body) {
      document.body.classList.add(BODY_CLASS);
    }
    ensureCartLayers();
    resetLayerVisibility();
    bindCartHandlers();
    ensureOptionResultHandler();
    setupPickListToggle();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGoodsList);
  } else {
    initGoodsList();
  }
})(window.jQuery);
