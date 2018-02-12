<?php
class RequestApi
{
    /**
     * CurlでAPIのリクエストを行う
     * [TODO] getTweetAPIからしか動作確認していない
     * 必要あれば今後拡張する
     *
     * @param string $request_url リクエストURL
     * @param array $context コンテキスト情報
     * @return boolean|mixed
     */
    public static function request($request_url, $context) {
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

        // POST系の場合
        if ($context['http']['method'] == 'POST' && isset($context['http']['content'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $context['http']['content']);
        }

        $res1 = curl_exec($curl);
        $res2 = curl_getinfo($curl);
        curl_close($curl);

        // 取得したデータ
        $json = substr($res1, $res2['header_size']);         // 取得したデータ(JSONなど)
        $header = substr($res1, 0, $res2['header_size']); // レスポンスヘッダー
        // JSONを配列に変換
        $aryRes = json_decode($json, true) ;

        return $aryRes;
    }

    /**
     * APIの並列リクエスト用メソッド
     *
     * @param array $curl_opts curl_multi用オプション
     * @return array
     */
    public static function multiRequest($curl_opts)
    {
        $res = [
            'result'   => "",
            'response' => []
        ];

        // curl_multi
        $mh = curl_multi_init();
        //  個別にオプション設定
        foreach ($curl_opts as $curl_opt) {
            // リクエストごとにオプション設定してマルチハンドラへ追加
            $ch = curl_init();
            curl_setopt_array($ch, $curl_opt);
            curl_multi_add_handle($mh, $ch);
        }

        // リクエスト開始
        do {
            $stat = curl_multi_exec($mh, $running);
        } while ($stat === CURLM_CALL_MULTI_PERFORM);
        // 処理失敗時
        if (!$running || $stat !== CURLM_OK) {
            $res["result"]   = "ng";
            $res["response"] = "Curlリクエストを開始できませんでした。設定をご確認ください。";
            return $res;
        }

        // レスポンスに対しての処理
        do {
            switch (curl_multi_select($mh, CURL_TIMEOUT)) {
                case -1: // select失敗時、間をおいてリトライする
                    usleep(10);
                    do {
                        $stat = curl_multi_exec($mh, $running);
                    } while ($stat === CURLM_CALL_MULTI_PERFORM);
                    continue 2;
                case 0:  //タイムアウト
                    $res["result"]   = "ng";
                    $res["response"] = "リクエストがタイムアウトしました。しばらく時間をおいて再度アクセスしてください。";
                    return $res;
                default: //どれかが成功 or 失敗した
                    do {
                        $stat = curl_multi_exec($mh, $running); //ステータスを更新
                    } while ($stat === CURLM_CALL_MULTI_PERFORM);

                    do {
                        if ($info = curl_multi_info_read($mh, $remains)) {
                            //変化のあったcurlハンドラを取得する
                            $key = curl_getinfo($info['handle'], CURLINFO_PRIVATE);
                            $response = curl_multi_getcontent($info['handle']);

                            if ($response === false) {
                                $res["result"]   = "ng";
                                $res["response"] = "通信エラー。しばらく時間をおいて再度アクセスしてください。";
                                return $res;
                            } else {
                                $res["response"][$key] = $response;
                            }
                            curl_multi_remove_handle($mh, $info['handle']);
                            curl_close($info['handle']);
                        }
                    } while ($remains);
            }
        } while ($running);
        curl_multi_close($mh);

        $res["result"] = "ok";
        return $res;
    }
}
