<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CurlClient
{
    public function request(string $method, string $url, array $opts = []): array
    {
        $start   = microtime(true);
        $method  = strtoupper($method);
        $headers = $opts['headers'] ?? [];
        $query   = $opts['query']   ?? [];
        $body    = $opts['body']    ?? null;
        $timeout = (int)($opts['timeout'] ?? config('net.req_t',4));
        $connect = (int)($opts['connect_timeout'] ?? config('net.con_t',2));
        $retries = (int)($opts['retries'] ?? 1);

        if ($query) {
            $url .= (str_contains($url,'?')?'&':'?').http_build_query($query);
        }

        $curlHeaders = [];
        foreach ($headers as $k => $v) $curlHeaders[] = $k . ': ' . $v;

        $attempt = 0;
        $respHeaders = []; $response = null; $err = null; $status = 0;

        do {
            $attempt++;
            $respHeaders = [];
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_HTTPHEADER     => $curlHeaders,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_CONNECTTIMEOUT => $connect,
                CURLOPT_HEADERFUNCTION => function($ch, $header) use (&$respHeaders){
                    $len = strlen($header);
                    $parts = explode(':', $header, 2);
                    if (count($parts) === 2) {
                        $respHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                    }
                    return $len;
                },
            ]);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? json_encode($body, JSON_UNESCAPED_UNICODE) : $body);
            }

            $response = curl_exec($ch);
            $err      = curl_errno($ch) ? curl_error($ch) : null;
            $status   = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            // retry em falha de rede/5xx
            $transient = $err || ($status >= 500 && $status <= 599);
            if ($transient && $attempt <= $retries) {
                usleep(150000 * $attempt);
            } else {
                break;
            }
        } while ($attempt <= $retries);

        $ms = (int) ((microtime(true) - $start) * 1000);
        Log::info('curl.request.done', [
            'method' => $method, 'url' => parse_url($url, PHP_URL_PATH),
            'status' => $status, 'error' => $err, 'latency_ms' => $ms,
            'attempts' => $attempt, 'affinity' => $respHeaders['sibs-affinity-host'] ?? null,
            'rid' => request()->header('X-Request-Id')
        ]);

        return [
            'status'  => (int) $status,
            'error'   => $err,
            'headers' => $respHeaders,
            'body'    => $response,
            'json'    => $this->tryJson($response),
        ];
    }

    private function tryJson(?string $s)
    {
        if ($s === null || $s === '') return null;
        $j = json_decode($s, true);
        return json_last_error() === JSON_ERROR_NONE ? $j : null;
    }
}
