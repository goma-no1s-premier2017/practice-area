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

    // twitter parameters
    protected $option_params;
    protected $now;

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

        $this->now = date("Ymd_his");
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
        return RequestApi::request($request_url, $context);
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

    /**
     * 必要な場合、事前にパラメータをセットする
     *
     * @param array $option_params
     * @return TwitterApiAction
     */
    public function PresetOptions($option_params)
    {
        if (isset($option_params) && is_array($option_params)) {
            $this->option_params = $option_params;
        }

        return $this;
    }

    /**
     * 一意のツイート画像を10件保存する
     *
     * @param array $aryKeyCheck
     * @param number $img_count
     * @param string $max_id
     * @param number $request_count
     * @return boolean
     */
    public function SaveUniqueTweetImages($aryKeyCheck = [], $img_count = 1, $max_id = "", $request_count = 1)
    {
        if ($request_count > MAX_REQUEST_COUNT) {
            return false;
        }

        $option_params = $this->option_params;

        if ($max_id !== "") {
            $option_params["max_id"] = $max_id; // $max_idより古いTweetを取得
        }

        // TwitterAPI連携
        $res = $this->Action("search/tweets", $option_params, "GET");
        if (!isset($res["statuses"]) || !is_array($res["statuses"])) {
            // Tweetを取得できなければ再試行
            $request_count++;
            return $this->SaveUniqueTweetImages($aryKeyCheck, $img_count, $max_id, $request_count);
        }

        foreach ($res["statuses"] as $status) {
            // 10個の画像を取得した時点で終了
            if ($img_count > 10) {
                break;
            }

            $max_id = $status["id_str"];

            // 画像がない || 同一画像ならスキップ
            if (!isset($status['entities']['media'][0]["media_url_https"]) ||
                isset($aryKeyCheck[$status['entities']['media'][0]["media_url_https"]])) {
                    continue;
                }

                // 画像データの取得
                $data = @file_get_contents($status['entities']['media'][0]["media_url_https"]);
                if ($data !== false) {
                    // パス付ファイル名作成
                    $ext = substr($status['entities']['media'][0]["media_url_https"], strrpos($status['entities']['media'][0]["media_url_https"], '.') + 1);
                    $file_path = DIR_TWEET_IMAGE . "tweet_img_{$this->now}_{$img_count}.{$ext}";

                    // 画像の保存
                    $result = @file_put_contents($file_path, $data);
                    if ($result !== false) {
                        // 保存に成功していればパスを出力
                        echo $file_path , PHP_EOL;

                        // 情報更新
                        $aryKeyCheck[$status['entities']['media'][0]["media_url_https"]] = true;
                        $img_count++;
                    }
                }
        }

        // 異なる画像を10個保存できれば終了。できていなければ再試行
        if ($img_count > 10) {
            return true;
        } else {
            $request_count++;
            return $this->SaveUniqueTweetImages($aryKeyCheck, $img_count, $max_id, $request_count);
        }
    }
}
