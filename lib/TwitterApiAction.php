<?php
/**
 * TwitterApi用の簡易クラス
 */
class TwitterApiAction
{
    const BASE_URL = "https://api.twitter.com/1.1/";

    protected $consumerKey;
    protected $consumerSecret;
    protected $accessToken;
    protected $accessTokenSecret;

    /**
     * コンストラクタ
     *
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $accessToken
     * @param string $accessTokenSecret
     */
    public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
    {
        $this->consumerKey       = $consumerKey;
        $this->consumerSecret    = $consumerSecret;
        $this->accessToken       = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
    }

    /**
     * TwitterApi実行処理
     * 一応、POSTにも対応させておく
     *
     * @param string $type   エンドポイントの種類
     * @param array  $option オプションパラメータ
     * @param string $method GET or POST
     *
     * @return array|string|boolean 実行結果
     */
    public function Action ($type, $option, $method)
    {
        // リクエストURL作成
        $request_url = self::BASE_URL . $type . '.json';

        // 手順に沿ってヘッダーパラメータを作成
        $header_params = $this->MakeHeaderParams($option, $request_url, $method);

        // リクエスト用のコンテキスト
        $context = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Authorization: OAuth ' . $header_params,
                ]
            ]
        ];

        // リクエストの形式毎にパラメータのセットを行う
        if ($method == 'GET' && !empty($option)) {
            $request_url .= '?' . http_build_query($option);
        } else if ($method == 'POST' && !empty($option)) {
            $context['http']['content'] = http_build_query($option);
        }

        // Apiコール
        return $this->RequestApi($request_url, $context);
    }

    /**
     * TwitterAPIのリクエストを行う
     *
     * @param string $request_url リクエストURL
     * @param array $context コンテキスト情報
     * @return boolean|mixed
     */
    protected function RequestApi($request_url, $context) {
        // curlでリクエストを実施する
        $curl = curl_init();
        if ($curl === false) {
            return false;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL            => $request_url,
            CURLOPT_HEADER         => 1,
            CURLOPT_CUSTOMREQUEST  => $context['http']['method'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $context['http']['header'],
            CURLOPT_TIMEOUT        => 10
        ]);

        // POST系エンドポイントの場合
        if ($context['http']['method'] == 'POST' && isset($context['http']['content'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $context['http']['content']);
        }

        $res1 = curl_exec($curl);
        $res2 = curl_getinfo($curl);
        curl_close($curl);

        // 取得したデータ
        $json = substr( $res1, $res2['header_size'] ) ;		 // 取得したデータ(JSONなど)
        $header = substr( $res1, 0, $res2['header_size'] ) ; // レスポンスヘッダー
        // JSONを配列に変換
        $aryRes = json_decode($json, true) ;

        return $aryRes;
    }

    /**
     * 所定の方法でヘッダ用のパラメータを作成する
     *
     * @param array $option オプションパラメータ
     * @param string $request_url URL
     * @param string $method GET or POST
     * @return string ヘッダ用のパラメータ
     */
    protected function MakeHeaderParams($option, $request_url, $method)
    {
        // 署名関連のパラメータ
        $auth_params = [
            'oauth_token'            => $this->accessToken,
            'oauth_consumer_key'     => $this->consumerKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => microtime(),
            'oauth_version'          => '1.0'
        ];

        $params = array_merge($option, $auth_params);
        ksort($params);

        $request_params = rawurlencode(str_replace(['+', '%7E'], ['%20', '~'], http_build_query($params, '', '&')));
        $encoded_method = rawurlencode($method);
        $encoded_url    = rawurlencode($request_url);

        $signature_data = $encoded_method . '&' . $encoded_url . '&' . $request_params;
        $signature_key  = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->accessTokenSecret);

        $params['oauth_signature'] = base64_encode(hash_hmac('sha1', $signature_data, $signature_key, true));

        return http_build_query($params, '', ',');
    }
}