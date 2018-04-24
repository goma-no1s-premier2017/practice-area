<?php
/**
 * TwitterAPIを利用して「@realDonaldTrump」の最新つぶやきを10件取得する
 */
// realDonaldTrumpのタイムゾーンに合わせておく
date_default_timezone_set('America/New_York');

// クラスファイルの読み込み
define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
require_once DIR_ROOT . 'define.php';
require_once DIR_LIB . 'RequestApi.php';
require_once DIR_LIB . 'TwitterApiAction.php';

// アクセス情報
$consumerKey       = "UhuBsj54DbRnnHGQYaPywHmAi";
$consumerSecret    = "e1zSaGoQorVKzyW5W7U8LaAmkUsf3nnjdIHCxh45Y03bnnBj86";
$accessToken       = "42566916-5CI2BDtmiUQdWFKtTY6ajgxmLlYjURI4ftW2K88h3";
$accessTokenSecret = "YHfdFB1RJxIcHQ6XOriX0GZhmZmFpNmI5wegaBIRDflIU";

// オプションのパラメータセット
$option_param = [
    "q"           => "from:realDonaldTrump", // @realDonaldTrumpの
    "result_type" => "recent",               // 最新ツイートを
    "tweet_mode"  => 'extended',             // extendedモードで
    "count"       => 10                      // 10件取得
];

// TwitterApiからデータを取得する
$twitterApiAction = new TwitterApiAction($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$res = $twitterApiAction->Action("search/tweets", $option_param, "GET");

$aryStatus = [];
if (isset($res['statuses']) && is_array($res['statuses'])) {
    $aryStatus = $res['statuses'];
} else {
    echo "つぶやきを取得できませんでした。" . PHP_EOL;
    exit;
}

// ツイートを出力
foreach ($aryStatus as $status) {
    // 日付をフォーマット
    $date = date('Y日m月d日 H時i分s秒', strtotime($status['created_at']));
    $text = $status['full_text'];

    echo "<<< {$date} >>>" . PHP_EOL;
    echo $text . PHP_EOL;
    echo "----------------------------" . PHP_EOL;
}
exit;
