<?php
/**
 * Google API用の簡易クラス
 * [TODO]201808テストで使用する最低限のものだけ実装
 */
class GoogleSheetsApi
{
    /**
     * ベースＵＲＬ
     * @var string
     */
    const BASE_URL = 'https://sheets.googleapis.com/v4/spreadsheets/';

    /**
     * Google API用のkey
     * @var string
     */
    protected $apiKey;

    /**
     * スプレッドシートＩＤ
     * @var string
     */
    protected $spreadsheetId;

    /**
     * シートＩＤ
     * @var string
     */
    protected $sheetId;

    /**
     * シート名
     * @var string
     */
    protected $sheetName;

    /**
     * コンストラクタ
     *
     * @param string $apiKey
     * @param string $targetUrl
     */
    public function __construct(string $apiKey, string $targetUrl)
    {
        // URLからスプレッドシートIDとシートIDを取得する
        if (preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)\/[a-zA-Z0-9-_\/]+[#&]+gid=([0-9]+)/', $targetUrl, $m) === 0) {
            throw new Exception('スプレッドシートIDもしくはシートIDを取得できませんでした。');
        }

        $this->apiKey = $apiKey;
        $this->spreadsheetId = $m[1];
        $this->sheetId = $m[2];
    }

    /**
     * Method:spreadsheets.values.get
     * 指定範囲に格納されている値を取得する
     *
     * @param string $range
     * @param array $options
     * @return boolean|mixed
     */
    public function getSheetValues(string $range, array $options = [])
    {
        $requestUrl = self::BASE_URL . "{$this->spreadsheetId}/values/{$range}?fields=values&key={$this->apiKey}";
        if (!empty($options)) {
            $requestUrl .= '&' . http_build_query($options);
        }

        $context = [
            'http' => [
                'method' => 'GET'
            ]
        ];

        return RequestApi::request($requestUrl, $context);
    }
}
