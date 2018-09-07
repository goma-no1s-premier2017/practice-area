/**
 * マリオアニメーション用JS
 */
var marioWalk = {
  displayWidth: null, // マリオが移動できる画面幅
  mPosition: 200,     // マリオの立ち位置
  displayEl: null,    // マリオが移動する画面のエレメント
  moveBoxEl: null,    // マリオ移動用ボックスのエレメント
  mWidth: null,       // マリオ横幅

  // [TODO]カーソルキーイベントのエラーハンドリングのためフラグを二つ作成
  isRightWalking: false, // 移動中判定フラグ
  isLeftWalking: false,  // 移動中判定フラグ

  // 初期処理。必要な値とイベントをセット
  initialize: function(displayEl, moveBoxEl, mWidth) {
    this.displayEl = $(displayEl); // 移動画面
    this.moveBoxEl = $(moveBoxEl); // マリオ移動用ボックス
    this.mWidth    = mWidth;       // マリオの横幅

    // マリオ移動幅(0px ~ ([移動画面幅] - [マリオの横幅])px)
    this.displayWidth = this.displayEl.innerWidth() - mWidth;
    // ※イベントセット
    // カーソルキーイベント
    $('html').keydown(function(e) { marioWalk.confirmCursorDirection(e, 'start'); });
    $('html').keyup(function(e) { marioWalk.confirmCursorDirection(e, 'end'); });
    // クリックorタップイベント
    $("#event-area").on('mousedown touchstart', displayEl, function(e) { marioWalk.confirmDirection(e, 'start'); });
    $("#event-area").on('mouseup touchend', displayEl, function(e) { marioWalk.confirmDirection(e, 'end'); });
    $("#event-border").on('mouseout', displayEl, function(e) {
      marioWalk.isRightWalking = false;
      marioWalk.isLeftWalking = false;
    });
  },
  // クリックしたカーソルの方向を判定
  confirmCursorDirection: function(e, action) {
    if (action === 'start') {
      // どちらに進むか判定（最新のクリックを優先する）
      switch(e.which) {
        case 39: // key[→]
          if (!marioWalk.isRightWalking) {
            // 右に進むフラグをたててアニメーション開始
            marioWalk.isRightWalking = true;
            marioWalk.isLeftWalking = false;
            marioWalk.doWalking('right');
          }
          break;
        case 37: // key[←]
          if (!marioWalk.isLeftWalking) {
            // 左に進むフラグをたててアニメーション開始
            marioWalk.isLeftWalking = true;
            marioWalk.isRightWalking = false;
            marioWalk.doWalking('left');
          }
        default: // それ以外は何もしない
      }
    } else if (action === 'end') {
      // 移動中フラグを立てる
      switch(e.which) {
        case 39: // key[→]
          marioWalk.isRightWalking = false;
          break;
        case 37: // key[←]
          marioWalk.isLeftWalking = false;
          break;
        default:
          marioWalk.isRightWalking = false;
          marioWalk.isLeftWalking = false;
          break;
      }
    }
  },
  // クリックorタップ時に進むべき方向を判定
  confirmDirection: function(e, action) {
    // イベントの多重発火防止
    e.preventDefault();

    // マリオが立っている中心点の位置を取得
    var cImgPosition = $('.active').offset().left + (marioWalk.mWidth / 2);
    var direction, pageX;

    // クリックorタップ終了時は移動中フラグを下ろして終了
    if (action === 'end') {
      marioWalk.isLeftWalking = false;
      marioWalk.isRightWalking = false;
      return true;
    }

    if (e.type === "touchstart") {
      pageX = e.changedTouches[0].pageX;
    } else {
      pageX = e.pageX;
    }

    if (pageX < cImgPosition && !marioWalk.isLeftWalking) {
      // マリオの画像の中心点より左側をクリック&&移動中フラグがたっていなければアニメーション開始
      marioWalk.isLeftWalking = true;
      marioWalk.isRightWalking = false;
      marioWalk.doWalking('left');
    } else if (pageX >= cImgPosition && !marioWalk.isRightWalking) {
      // マリオの画像の中心点から右側をクリック&&移動中フラグがたっていなければアニメーション開始
      marioWalk.isRightWalking = true;
      marioWalk.isLeftWalking = false;
      marioWalk.doWalking('right');
    }
  },
  // マリオの歩行アニメーション
  doWalking: function(direction) {
    var current = 0;
    var next, dScroll;

    // 進む方向から、背景スクロール方向用クラスを取得
    if (direction === 'right') {
      dScroll = 'bgscroll-r';
    } else if (direction === 'left') {
      dScroll = 'bgscroll-l';
    }

    var timer = setInterval(function() {
      // 次のマリオ画像
      if (current === 0) {
        next = 1;
      } else {
        next = 0;
      }

      // ====マリオ画像の移動====
      // cssでマリオの左端はleft:0となるよう設定
      if (direction === 'left' && (marioWalk.mPosition - 20) > 0) {
        // 左歩行。マリオ位置がマイナスにならなければ本体が移動
        marioWalk.mPosition -= 20;
        marioWalk.moveBoxEl.css({left : marioWalk.mPosition});
      } else if (direction === 'right' && (marioWalk.mPosition + 20) < marioWalk.displayWidth) {
        // 右歩行。マリオ位置が移動限界幅をこえなければ本体が移動
        marioWalk.mPosition += 20;
        marioWalk.moveBoxEl.css({left : marioWalk.mPosition});
      } else if (!marioWalk.displayEl.hasClass(dScroll)) {
        // マリオが左端・右端についた場合、背景をスクロールさせてマリオが歩行していると見せかける
        marioWalk.displayEl.addClass(dScroll);
      }

      // ====マリオ画像の切り替え====
      $('#mario-' + direction + next).addClass('active');
      if ($('#mario-left-stand').hasClass('active') || $('#mario-right-stand').hasClass('active')) {
        // 移動開始時は仁王立ち画像も消す
        $('#mario-left-stand, #mario-right-stand').removeClass('active');
      }
      $('#mario-' + direction + current).removeClass('active');

      // 現在のマリオを更新
      current = next;

      // ====アニメーション終了判定====
      // Objectの特性を利用。confirmファンクションでフラグを下ろすと終了する
      if ((direction === 'left' && !marioWalk.isLeftWalking)
          || (direction === 'right' && !marioWalk.isRightWalking)) {
        $('#mario-' + direction + '-stand').addClass('active');
        $('#mario-' + direction + '0, #mario-' + direction + '1').removeClass('active');
        marioWalk.displayEl.removeClass(dScroll);
        clearInterval(timer);
      }
    }, 100);
  }
};
