<?php
/**
 * 定義ファイル
*/
date_default_timezone_set('Asia/Tokyo');

define("DIR_LIB", DIR_ROOT . "lib/");          // ライブラリ
define("DIR_PHPEXCEL", DIR_LIB . "phpexcel/"); // PHPExcel用ディレクトリ

define("DIR_BAT", DIR_ROOT . "bat/");       // バッチファイル
define("DIR_BAT_2017", DIR_ROOT . "2017/"); // 2017年度バッチファイル
define("DIR_BAT_2018", DIR_ROOT . "2018/"); // 2018年度バッチファイル

define("DIR_FILES", DIR_ROOT . "files/"); // 出力ファイル格納
define("DIR_CSV",         DIR_FILES . "csv/");       // CSVファイル
define("DIR_EXCEL",       DIR_FILES . "excel/");     // Excelファイル
define("DIR_TXT",         DIR_FILES . "txt/");       // txtファイル
define("DIR_QR_CODE",     DIR_FILES . "qrcode/");    // qrcode
define("DIR_TWEET_IMAGE", DIR_FILES . "tweet_img/"); // tweet画像

define("PATH_MECAB_DICT", "/usr/local/lib/mecab/dic/mecab-ipadic-neologd");

define("CURL_TIMEOUT", 10); // Curlリクエストのタイムアウト時間

// TwitterAPI用のトークン
define("CONSUMER_KEY",        "");
define("CONSUMER_SECRET",     "");
define("ACCESS_TOKEN",        "");
define("ACCESS_TOKEN_SECRET", "");

define("MAX_REQUEST_COUNT", 10); // TwitterAPIリクエストの最大試行回数
