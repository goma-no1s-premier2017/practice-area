<?php
/**
 * https://premier.no1s.biz/
 * に自動ログイン、
 * 商品データのテキストを自動抽出、
 * CSV形式のファイルに保存する
 *
 * 201801プレミア問一
 */
try {
    // クラスファイルの読み込み
    define("DIR_ROOT", preg_replace('/bat$/', '', dirname(__FILE__)));
    require_once DIR_ROOT . 'define.php';
    require_once DIR_LIB . 'FilesController.php';
    require_once DIR_LIB . 'WebScrapingAction.php';

    // ログイン情報
    $email = "micky.mouse@no1s.biz";
    $password = "micky";
    $login_url  = "https://premier.no1s.biz/";

    //===================
    // 自動ログイン処理
    //===================
    echo "トークンの発行　　...";
    $context = [
        "http" => [
            "method" => "GET"
        ]
    ];

    // ログイン画面へアクセスを行い、トークンを発行
    list($response, $http_response_header) = FilesController::fileGetContents($login_url, $context, true);
    $csrfToken = FilesController::getToken($http_response_header, "csrfToken");
    // 取得チェック
    if ($response === false || $csrfToken == "") {
        throw new Exception("CSRFトークンの発行に失敗しました。");
    }
    echo "OK" . PHP_EOL;

    echo "自動ログイン　　　...";
    // リクエスト情報を設定
    $post_request = [
        "email"      => $email,
        "password"   => $password,
        "_method"    => "POST",
        "_csrfToken" => $csrfToken
    ];
    $query = http_build_query($post_request);

    $header = [
        "Content-Type: application/x-www-form-urlencoded",
        "Content-Length: " . strlen($query),
        "Cookie: csrfToken={$csrfToken}"
    ];

    $context = [
        "http" => [
            "method"  => "POST",
            "header"  => implode("\r\n", $header),
            "content" => $query
        ]
    ];

    // ログイン時に発行されるharumafujiTokenを取得
    list($response, $http_response_header) = FilesController::fileGetContents($login_url, $context, true);
    $harumafujiToken = FilesController::getToken($http_response_header, "harumafuji");

    // 取得チェック
    if ($response === false || $harumafujiToken == "") {
        throw new Exception("harumafujiトークンの発行に失敗しました。");
    }
    echo "OK" . PHP_EOL;

    //===================
    // ログイン後の処理
    //===================
    echo "商品データ抽出　　...";
    $admin_url = "https://premier.no1s.biz/admin";
    $headerToken = " csrfToken={$csrfToken}; harumafuji={$harumafujiToken}; path=/; secure; HttpOnly";
    // 発行したトークンをクッキーにセットする
    $header = [
        "Cookie: " . $headerToken
    ];

    $context = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", $header)
        ]
    ];

    // [TODO]現在のページは1 - 3
    $html = "";
    $count = 1;
    while (true) {
        // 各ページのデータを取得する
        list($response, $http_response_header) = FilesController::fileGetContents("{$admin_url}?page={$count}", $context, true);

        // [TODO]今回のWebページでは404エラーで終了
        if (preg_match("/404 Not Found/", $http_response_header[0]) !== 0) {
            break;
        }

        $html .= $response;
        $count++;
    }

    // htmlデータの取得チェック
    if ($html == "") {
        throw new Exception("商品データを抽出できませんでした。");
    }
    echo "OK" . PHP_EOL;

    echo "CSVファイル出力　 ...";
    // フルパス付のファイル名
    $now  = date("Ymd_His");
    $file = DIR_CSV . "ProductData_{$now}.csv";

    // ウェブスクレイピングで商品データを取得
    $WebScrapingAction = new WebScrapingAction('', false);
    $WebScrapingAction->setHtml($html);
    // tdタグ内に商品データが入っている
    $elements = $WebScrapingAction->getElementsByXpath("//td");

    $csvData = "";
    $count = 1;
    foreach ($elements as $element) {
        // 文字コード変換のため、取得した商品データをＣＳＶ用の文字列に編集する
        $csvData .= "\"{$element->nodeValue}\",";
        if ($count % 3 === 0) {
            $csvData .= "\n";
        }
        $count++;
    }
    // UTF-8に変換
    $csvData = mb_convert_encoding($csvData, "UTF-8");
    if ($csvData == "") {
        throw new Exception("商品データの編集に失敗しました。");
    }

    if (!file_exists(DIR_CSV)) {
        if (!mkdir(DIR_CSV, 0777)) {
            throw new Exception("CSVファイル格納用ディレクトリの作成に失敗しました。");
        }
    }

    // CSVファイルに書き込み
    $fp = fopen($file, "w");
    if ($fp !== false) {
        fwrite($fp, $csvData);
        fclose($fp);
    } else {
        throw new Exception("CSVファイルの書き込みに失敗しました。");
    }
    echo "OK" . PHP_EOL;
    echo $file . PHP_EOL;
    exit;
} catch (Exception $e) {
    echo "NG" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
}
