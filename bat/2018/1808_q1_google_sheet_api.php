<?php
try {
    // クラスファイルの読み込み
    define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
    require_once DIR_ROOT . 'define.php';
    require_once DIR_LIB . 'RequestApi.php';
    require_once DIR_LIB . 'GoogleSheetsApi.php';

    $targetUrl = 'https://docs.google.com/spreadsheets/d/11BCnspCt2Mut3nhc4WMY6CYTd0zF9C3eCzsk1AEpKLM/edit#gid=0';

    // Google Sheets API実行
    $GoogleSheetsApi = new GoogleSheetsApi(GOOGLE_API_KEY, $targetUrl);
    $result = $GoogleSheetsApi->getSheetValues('sales!A1:E6', ['fields' => 'values']);

    // resultチェック
    if ($result === false) {
        throw new Exception('通信エラーが発生しました。');
    } elseif (empty($result['values'])) {
        throw new Exception('指定セルに値が存在しませんでした。');
    }

    // 取得データを仕様に沿った形になるよう整形
    $output = '';
    foreach ($result['values'] as $row) {
        $output .= "'" . implode("','", $row) . "',\n";
    }
    // UTF-8に変換して出力
    echo mb_convert_encoding($output, "UTF-8");
} catch (Exception $e) {
    echo $e->getMessage() , PHP_EOL;
}
