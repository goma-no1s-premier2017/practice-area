<?php
/**
 * 「沖縄 高級ホテル」のGoogle検索結果からタイトルとリンク先を取得する
 */
// クラスファイルの読み込み
$lib_path = preg_replace('/bat\/[a-zA-z0-9]+/', '/lib/', dirname(__FILE__));
require_once $lib_path . 'WebScrapingAction.php';

$WebScrapingAction = new WebScrapingAction('https://www.google.co.jp/search?q=%E6%B2%96%E7%B8%84%E3%80%80%E9%AB%98%E7%B4%9A%E3%83%9B%E3%83%86%E3%83%AB');
// google searchのリンクはclass=r直下のaタグにセットされている
$elements = $WebScrapingAction->getElementsByXpath("//*[@class='r']/a");

foreach ($elements as $element) {
    $nodes = $element->childNodes;

    // タイトル
    $title = $element->nodeValue;
    // Google検索のURLはhref内のqパラメータにセットされている
    $res = preg_match('/[\?|\&]q=(.+?)&/', $element->getAttribute('href'), $m);
    if ($res === 1) {
        $url = $m[1];
    } else {
        $url = '-';
    }

    echo "<<< {$title} >>>" . PHP_EOL;
    echo $url . PHP_EOL;
    echo "----------------------------" . PHP_EOL;
}
exit;
