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

    // [TODO]せっかくなのでクロージャ×再帰表現で実装
    /* 異なるツイート画像を10件取得する
     *
     * @param array  $aryKeyCheck   Tweet画像チェック用配列([TODO] URLで同じかどうか判定)
     * @param int    $img_count     取得した画像数
     * @param string $max_id        TweetID
     * @param int    $request_count TwitterAPIのリクエスト回数
     *
     * @param $getTweetImage    再帰処理用のクロージャ
     * @param $twitterApiAction TwitterAPI用の自作クラス
     * @param $now              現在時刻
     *
     * @return true or false
     */
    $getTweetImage = function ($aryKeyCheck = [], $img_count = 1, $max_id = "", $request_count = 1) use (&$getTweetImage, &$twitterApiAction, &$now) {        // TwitterAPIリクエストの最大試行回数を超えても画像を取得できない場合は処理失敗とする
        if ($request_count > MAX_REQUEST_COUNT) {
            return false;
        }

        // オプションのパラメータセット
        $option_param = [
            "q"           => "JustinBieber filter:images -RT", // 「JustinBieber」と画像が含まれる、リツイートを除いた
            "result_type" => "recent",    // 最新ツイートを (正確に別ツイートを取得するため)
            "tweet_mode"  => "extended",  // extendedモードで
            "count"       => 10,          // 10件取得
        ];

        if ($max_id !== "") {
            $option_param["max_id"] = $max_id; // $max_idより古いTweetを取得
        }

        // TwitterAPI連携
        $res = $twitterApiAction->Action("search/tweets", $option_param, "GET");
        if (!isset($res["statuses"]) || !is_array($res["statuses"])) {
            // Tweetを取得できなければ再試行
            $request_count++;
            return $getTweetImage($aryKeyCheck, $img_count, $max_id, $request_count);
        }

        foreach ($res["statuses"] as $status) {
            // 10個の画像を取得した時点で終了
            if ($img_count > 10) {
                break;
            }

            $max_id = $status["id_str"];

            // 画像がない || 同一画像ならスキップ
            if (!isset($status['entities']['media'][0]["media_url_https"]) ||
                isset($aryKeyCheck[$status['entities']['media'][0]["media_url_https"]])) {
                continue;
            }

            // 画像データの取得
            $data = @file_get_contents($status['entities']['media'][0]["media_url_https"]);
            if ($data !== false) {
                // パス付ファイル名作成
                $ext = substr($status['entities']['media'][0]["media_url_https"], strrpos($status['entities']['media'][0]["media_url_https"], '.') + 1);
                $file_path = DIR_TWEET_IMAGE . "{$now}_{$img_count}.{$ext}";

                // 画像の保存
                $result = @file_put_contents($file_path, $data);
                if ($result !== false) {
                    // 保存に成功していればパスを出力
                    echo $file_path , PHP_EOL;

                    // 情報更新
                    $aryKeyCheck[$status['entities']['media'][0]["media_url_https"]] = true;
                    $img_count++;
                }
            }
        }

        // 異なる画像を10個保存できれば終了。できていなければ再試行
        if ($img_count > 10) {
            return true;
        } else {
            $request_count++;
            return $getTweetImage($aryKeyCheck, $img_count, $max_id, $request_count);
        }
    }; // $getTweetImage end

    // ディレクトリの存在チェック
    if (!file_exists(DIR_TWEET_IMAGE)) {
        if (!mkdir(DIR_TWEET_IMAGE, 0777)) {
            throw new Exception("Tweet画像保存用ディレクトリの作成に失敗しました。");
        }
    }

    // Tweet画像保存処理
    $twitterApiAction = new TwitterApiAction(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
    $now = date("Ymd_his");

    // 画像を10件取得するまで再帰的にAPIリクエストを行う
    if (!$getTweetImage()) {
        throw new Exception("Tweet画像を10件取得できませんでした。");
    }
} catch (Exception $e) {
    echo $e->getMessage() , PHP_EOL;
}
