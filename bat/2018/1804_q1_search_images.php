<?php
/**
 * TwitterAPIを利用してJustinBieberが文言内に含まれるツイートの画像を10個取得する
 */
try {
    // クラスファイルの読み込み
    define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
    require_once DIR_ROOT . 'define.php';
    require_once DIR_LIB . 'RequestApi.php';
    require_once DIR_LIB . 'TwitterApiAction.php';

    // ディレクトリの存在チェック
    if (!file_exists(DIR_TWEET_IMAGE)) {
        if (!mkdir(DIR_TWEET_IMAGE, 0777)) {
            throw new Exception("Tweet画像保存用ディレクトリの作成に失敗しました。");
        }
    }

    // オプションのパラメータセット
    $option_params = [
        "q"           => "JustinBieber filter:images -RT", // 「JustinBieber」と画像が含まれる、リツイートを除いた
        "result_type" => "recent",    // 最新ツイートを (正確に別ツイートを取得するため)
        "tweet_mode"  => "extended",  // extendedモードで
        "count"       => 10,          // 10件取得
    ];

    // 画像を10件取得するまで再帰的にAPIリクエストを行う
    $twitterApiAction = new TwitterApiAction(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
    if (!$twitterApiAction->PresetOptions($option_params)->SaveUniqueTweetImages()) {
        throw new Exception("Tweet画像を10件取得できませんでした。");
    }

    echo "Tweet画像を10件取得しました。" , PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage() , PHP_EOL;
}
