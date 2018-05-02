<?php
/**
 * no1sサイト内のクローリング処理を実施
 */
try {
    // クラスファイルの読み込み
    define("DIR_ROOT", preg_replace('/bat\/[a-zA-z0-9]+/', '', dirname(__FILE__)));
    require_once DIR_ROOT . 'define.php';
    require_once DIR_LIB . 'RequestApi.php';
    require_once DIR_LIB . 'WebScrapingAction.php';

    $base_url = 'https://no1s.biz/';

    // WEBクローラ実行
    $WebScrapingAction = new WebScrapingAction("", false);
    if ($WebScrapingAction->WebCrawling($base_url)) {
        echo $base_url , '内のクローリングが正常に完了しました。', PHP_EOL;
    } else {
        throw new Exception("{$base_url}内のクローリングが中断されました。");
    }
} catch (Exception $e) {
    echo $e->getMessage() , PHP_EOL;
}
