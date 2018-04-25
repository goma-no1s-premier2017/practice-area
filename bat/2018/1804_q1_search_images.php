<?php
/**
 * TwitterAPIを利用して「JustinBieber」がアップした画像を10個取得する
 */
try {
    // クラスファイルの読み込み
    define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
    require_once DIR_ROOT . 'define.php';
    require_once DIR_LIB . 'RequestApi.php';
    require_once DIR_LIB . 'TwitterApiAction.php';

    // オプションのパラメータセット
    $option_param = [
        "q"          => "JustinBieber filter:images exclude:retweets", // 「JustinBieber」と画像が含まれる、リツイートを除いたツイートを
        "tweet_mode" => "extended",  // extendedモードで
        "count"      => 10           // 10件取得
    ];

    // twitterAPI実行
    $twitterApiAction = new TwitterApiAction(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
    $res = $twitterApiAction->Action("search/tweets", $option_param, "GET");
    if (!isset($res["statuses"]) || !is_array($res["statuses"])) {
        throw new Exception("つぶやきを取得できませんでした。");
    }

    // ディレクトリの存在チェック
    if (!file_exists(DIR_TWEET_IMAGE)) {
        if (!mkdir(DIR_TWEET_IMAGE, 0777)) {
            throw new Exception("Tweet画像保存用ディレクトリの作成に失敗しました。");
        }
    }

    // Tweet画像保存処理
    $now = date("Ymd_his");
    $count = 1;
    foreach ($res["statuses"] as $status) {
        // 万が一画像がなければスキップ
        if (!isset($status['entities']['media'][0]["media_url_https"])) {
            continue;
        }

        // 画像データの取得
        $data = @file_get_contents($status['entities']['media'][0]["media_url_https"]);
        if ($data !== false) {
            // パス付ファイル名作成
            $ext = substr($status['entities']['media'][0]["media_url_https"], strrpos($status['entities']['media'][0]["media_url_https"], '.') + 1);
            $file_path = DIR_TWEET_IMAGE . "{$now}_{$count}.{$ext}";

            // 画像の保存
            $result = @file_put_contents($file_path, $data);
            if ($result !== false) {
                // 保存に成功していればパスを出力
                echo $file_path , PHP_EOL;
                $count++;
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage() , PHP_EOL;
}
