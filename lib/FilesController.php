<?php
/**
 * ウェブアクセス関連処理
 */
class FilesController
{
    public function fileGetContents($url, $context = [], $need_header = false)
    {
        if (isset($cnotext['http']['method'])) {
            return false;
        }

        $response = @file_get_contents($url, false, stream_context_create($context));

        if ($need_header) {
            return [$response, $http_response_header];
        } else {
            return $response;
        }
    }

    public function getToken($http_response_header, $token_name)
    {
        if (!is_array($http_response_header)) {
            return "";
        }

        // レスポンスヘッダからクッキー情報を取得する
        $cookies = [];
        foreach ($http_response_header as $res) {
            if (strpos($res, ":") === false) {
                continue;
            }
            list($key, $value) = explode(":", $res);
            if ($key == "Set-Cookie") {
                $cookies[] = $value;
            }
        }

        $token = "";
        foreach ($cookies as $cookie) {
            if (preg_match("/{$token_name}=(.*?); /", $cookie, $m) !== 0) {
                $token = $m[1];
                break;
            }
        }

        return $token;
    }
}
