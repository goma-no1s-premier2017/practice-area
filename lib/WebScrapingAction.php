<?php
/**
 * WebScraping用の簡易クラス
 */
class WebScrapingAction
{
    protected $html;

    /**
     * コンストラクタ
     *
     * @param string $url スクレイピング対象URL
     */
    public function __construct($url = '', $isGetContents = true)
    {
        if ($isGetContents === true) {
            // 文字コード対策をしたHTML情報を保管しておく
            $html = @file_get_contents($url);
            if ($html === false) {
                echo '指定のURLを読み込むことが出来ませんでした。' . PHP_EOL;
                exit;
            } else {
                $this->html = mb_convert_encoding($html, "utf-8", "sjis-win");
            }
        }
    }

    /**
     * htmlをxpath形式に変換したデータから指定のDOM情報を返却する
     *
     * @param string $xpath 取得したい箇所のXPath
     * @return object 処理成功：DOMNodeList
     */
    public function getElementsByXpath($xpath)
    {
        $dom = new DOMDocument();
        // [TODO]特殊なタグが含まれるHTMLだとwarningが出るためエラー抑制
        @$dom->loadHTML($this->html);

        $dom_xpath = new DOMXPath($dom);
        $elements = $dom_xpath->query($xpath);

        if ($elements !== false) {
            return $elements;
        } else {
            echo 'エレメントを正しく取得できませんでした。' . PHP_EOL;
            exit;
        }
    }

    public function setHtml($html)
    {
        $this->html = mb_convert_encoding($html, "HTML-ENTITIES", "auto");
    }
}
