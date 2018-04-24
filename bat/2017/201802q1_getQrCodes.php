<?php
// クラスファイルの読み込み
define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
require_once DIR_ROOT . 'define.php';
require_once DIR_LIB . 'RequestApi.php';

// curl用のオプションを作成
$api = "https://api.qrserver.com/v1/create-qr-code/";
$urls = [
    "giants" => "http://www.giants.jp/top.html",
    "silent" => "https://www.amazon.co.jp/dp/B01BHPEC9G",
    "cosme"  => "http://www.cosme.net/product/product_id/10023860/top"
];
$base_params = [
    "size"   => "150x150", // 画像サイズ
    "format" => "png"      // 形式
];
$curl_opts = [];
foreach ($urls as $key => $url) {
    $base_params["data"] = $url;
    $curl_opts[] = [
        CURLOPT_URL            => "{$api}?" . http_build_query($base_params),
        CURLOPT_PRIVATE        => $key,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => CURL_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => CURL_TIMEOUT,
    ];
}
// curl_multi実行
$response = RequestApi::multiRequest($curl_opts);
if ($response["result"] === "ng") {
    echo $response["response"] . PHP_EOL;
    exit;
}

// 取得ファイル保存処理
$now = date("Ymd_his");
if (!file_exists(DIR_QR_CODE)) {
    if (!mkdir(DIR_QR_CODE, 0777)) {
        echo "QRコードファイル格納用ディレクトリの作成に失敗しました。" . PHP_EOL;
        exit;
    }
}
foreach ($response["response"] as $key => $png_data) {
    $file = DIR_QR_CODE . "qr_{$key}_{$now}.png";
    file_put_contents($file, $png_data);
    echo $file . PHP_EOL;
}
exit;
