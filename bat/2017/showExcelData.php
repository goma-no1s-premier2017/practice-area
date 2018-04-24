<?php
/**
 * エクセルデータを抽出・表示する
 *
 * 20180116プレミア問二
 */
// クラスファイルの読み込み
define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
require_once DIR_ROOT . 'define.php';
require_once DIR_LIB . 'ExcelController.php';

// エクセルから成績表を配列で取得
$ExcelController = new ExcelController(DIR_EXCEL . "data.xlsx");
$aryExcelData    = $ExcelController->getTableData("students", 1, 3);

// ヘッダー出力
foreach ($aryExcelData["header"] as $value) {
    // 全角2、半角1として文字列長を取得しておく
    $length = strlen(mb_convert_encoding($value, "SJIS-win", "UTF-8"));

    if ($value == "姓") {
        echo "名前" . str_pad("", 8, " ");
    } elseif ($value === "名") {
        continue;
    } else {
        echo $value . str_pad("", (7 - $length), " ");
    }
}
echo "合計点" . PHP_EOL;

// 個人個人のデータを出力する
foreach ($aryExcelData["body"] as $row_data) {
    $sum = 0;
    foreach ($aryExcelData["header"] as $header) {
        // 空白セル対応
        if (!isset($row_data[$header])) {
            $row_data[$header] = "";
        }

        if ($header == "姓") {
            $family_name = $row_data[$header];
        } elseif ($header == "名") {
            $name = "{$family_name} {$row_data[$header]}";
            echo $name . str_pad("", (12 - strlen(mb_convert_encoding($name, "SJIS-win", "UTF-8"))), " ");
        } else {
            if (is_numeric($row_data[$header])) {
                $sum += $row_data[$header];
            }
            echo $row_data[$header] . str_pad("", (7 - strlen(mb_convert_encoding($row_data[$header], "SJIS-win", "UTF-8"))), " ");
        }
    }
    echo $sum . PHP_EOL;
}
exit;
