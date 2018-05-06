<?php
/**
 * WebScraping用の簡易クラス
*/
class WebScrapingAction
{
    protected $html;

    protected $aryUniqueCheck;
    protected $baseUrlPattern;
    protected $titlePattern = '/<title>[\n]*(.*?)[\n]*<\/title>/i';

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
                $this->html = mb_convert_encoding($html, "utf-8", "auto");
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

    /**
     * ウェブクローリング
     * [TODO]重い。。処理方法要検討。
     * titleタグ取得がネックで
     *
     * @param string $url
     * @return boolean
     */
    public function WebCrawling($url)
    {
        // 初回のURLをベースと判断する
        if (!isset($this->baseUrlPattern)) {
            $this->baseUrlPattern = '/' . preg_quote($url, '/') . '/';
        }

        if (isset($this->aryUniqueCheck[$url]) || preg_match($this->baseUrlPattern, $url) === 0) {
            return true;
        }

        // htmlデータを取得
        $this->html = @file_get_contents($url);
        if ($this->html === false) {
            echo 'HTML取得エラー' , PHP_EOL;
            return false;
        } else {
            $this->html = mb_convert_encoding($this->html, "utf-8", "auto");
        }

        // URLとページタイトルを出力
        if (preg_match($this->titlePattern, $this->html, $title) === 1) {
            echo $url, "\t", $title[1], PHP_EOL;

            // URLユニークチェック変数にurlを追加
            $this->aryUniqueCheck[$url] = true;
        } else {
            echo 'タイトル取得エラー' , PHP_EOL;
            return false;
        }

        $elements = $this->getElementsByXpath('//a/@href');
        foreach ($elements as $element) {
            // href内のリンクを取得
            $href = $element->nodeValue;
            // 再起的に処理を実施
            if (!$this->WebCrawling($href)) {
                echo 'Webスクレイピングエラー' , PHP_EOL;
                return false;
            }
        }

        // 全て正常に処理されるとここにたどり着く
        return true;
    }

    /**
     * ウェブクローリング２
     * 並列リクエスト処理
     *
     * [TODO]要調査 500,503エラーが出たり、htmlが正しく取得できない場合がある
     *
     * @param mixed $urls 初回だけstring
     * @return boolean
     */
    public function WebCrawling2($urls)
    {
        // 初回のURLをベースと判断する
        if (!isset($this->baseUrlPattern)) {
            $this->baseUrlPattern = '/' . preg_quote($urls, '/') . '/';
            $urls = [$urls];
        }

        $curl_opts = [];
        // $count_key = 0;
        foreach ($urls as $count => $url) {
            // 出力済みURL || 対象外URLの場合処理終了
            if (isset($this->aryUniqueCheck[$url]) || preg_match($this->baseUrlPattern, $url) === 0) {
                continue;
            }

            $this->aryUniqueCheck[$url] = true;

            $curl_opts[] = [
                    CURLOPT_URL            => $url,
                    CURLOPT_PRIVATE        => $url,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => CURL_TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => CURL_TIMEOUT,
                    // リダイレクト対策
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 100,
                    CURLOPT_AUTOREFERER    => true,
            ];
        }
        if (empty($curl_opts)) {
            return true;
        }

        $response = RequestApi::multiRequest($curl_opts);
        if ($response["result"] === "ng") {
            echo $response['response'] , PHP_EOL;
            return false;
        }

        $concat_html = '';
        foreach ($response['response'] as $url => $html) {
            // [TODO]URLとページタイトルを出力
            if (preg_match($this->titlePattern, $html, $title) === 1) {
                echo $url, "\t", $title[1] . PHP_EOL;
                $concat_html .= $html;
            } else {
                var_dump($url);
                print_r($html);
                echo 'タイトル取得エラー', PHP_EOL;
                return false;
            }
        }
        $this->html = $concat_html;

        $hrefs = [];
        $elements = $this->getElementsByXpath('//a/@href');
        foreach ($elements as $element) {
            // href内のリンクを取得
            $hrefs[] = $element->nodeValue;
        }

        return $this->WebCrawling2($hrefs);
    }
}
