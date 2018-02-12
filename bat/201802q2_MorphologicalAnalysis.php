<?php
define("DIR_ROOT", preg_replace('/bat$/', '', dirname(__FILE__)));
require_once DIR_ROOT . 'define.php';

// 課題のテキストを取得
$file = DIR_TXT . "data.txt";
$txt  = file_get_contents($file);

// [TODO]php7系のインスタンス化記述
$mecab = new \MeCab\Tagger(["-d", PATH_MECAB_DICT]);
$nodes = $mecab->parseToNode($txt);

// 名詞配列の取得
// キー：抽出した名詞、要素：テキスト内に出てきた件数
$aryNouns = [];
foreach ($nodes as $node) {
    if (preg_match("/^名詞,/", $node->getFeature()) === 1) {
        $key = $node->getSurface();
        if (isset($aryNouns[$key])) {
            $aryNouns[$key]++;
        } else {
            $aryNouns[$key] = 1;
        }
    }
}
// 要素を基準に降順ソート
arsort($aryNouns, SORT_NUMERIC);
// 名詞と出現回数を表示する
foreach ($aryNouns as $noun => $count) {
    echo "{$noun}\t{$count}" . PHP_EOL;
}
