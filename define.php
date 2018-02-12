<?php
/**
 * 定義ファイル
*/
date_default_timezone_set('Asia/Tokyo');

define("DIR_LIB", DIR_ROOT . "lib/");          // ライブラリ
define("DIR_PHPEXCEL", DIR_LIB . "phpexcel/"); // PHPExcel用ディレクトリ

define("DIR_BAT", DIR_ROOT . "bat/");          // バッチファイル

define("DIR_FILES", DIR_ROOT . "files/");      // 出力ファイル格納
define("DIR_CSV", DIR_FILES . "csv/");         // CSVファイル
define("DIR_EXCEL", DIR_FILES . "excel/");     // Excelファイル
define("DIR_TXT", DIR_FILES . "txt/");         // Excelファイル
define("DIR_QR_CODE", DIR_FILES . "qrcode/");  // qrcodeファイル

define("PATH_MECAB_DICT", "/usr/local/lib/mecab/dic/mecab-ipadic-neologd");

define("CURL_TIMEOUT", 10); // Curlリクエストのタイムアウト時間
