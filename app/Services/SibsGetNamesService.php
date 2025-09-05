<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SibsGetNamesService
{
    private $aff;
    private $http;

    public function __construct( CurlClient $http,  SibsAffinity $aff) {
        $this->http = $http;
        $this->aff = $aff;

    }

    public function getNames(string $entity, string $reference): array
    {

        try {

            \Log::info('SibsGetNamesService:getNames');
            \Log::info('Entidade ' . $entity);
            \Log::info('Referencia ' . $reference);

            $base = $this->aff->apply(rtrim(config('net.sibs_base'), '/'));
            \Log::info('Base URL ' . $base);

            $ver  = config('net.sibs_ver','v1');
            \Log::info('Versão ' . $ver);

            $url  = sprintf('%s/sibs/%s/payment-owners/%s/%s', $base, $ver, $entity, $reference);

            $sec='aI2tK1vL3fG4gB5rV7qY4pI4wH2fY8mA8hP2nJ2hC1yM5kB8rY';

            \Log::info('URL ' . $url);

            $headers = [
                'Accept'          => 'application/json',
                'x-ibm-client-id' => config('net.sibs_id'),
                'X-Message-ID'    => $this->msgId14(),
            ];
            if ($b = config('net.bearer')) {
                $headers['Authorization'] = 'Bearer '.$b; // normalmente não precisa nesta operação
            }
            // se o product usa api key com secret em header:
            // if ($sec = config('net.sibs_secret_id')) {
            //     $headers['x-ibm-client-secret'] = $sec;
            // }

        // $basic = base64_encode(config('net.sibs_id') . ':' . config('net.sibs_secret_id'));
            //$headers['Authorization'] = 'Basic ' . $basic;

            Log::info('sibs.getnames.request', [
                'entity' => $entity, 'reference' => $reference,
                'url' => $url, 'rid' => request()->header('X-Request-Id')
            ]);

            $resp = $this->http->request('GET', $url, ['headers' => $headers, 'retries' => 2]);

            \Log::info('sibs.getnames.resp'. print_r($resp, true));

            $this->aff->rememberFrom($resp['headers']);

            if ($resp['error']) {
                \Log::warning('sibs.getnames.network_error', ['error' => $resp['error'], 'rid' => request()->header('X-Request-Id')]);
                return $this->err('UPSTREAM_ERROR','Falha de rede/timeout.',503);
            }

            $status = $resp['status'];
            $j      = $resp['json'] ?? [];
            $tx     = $j['transactionStatus'] ?? null; // ACTC/RJCT

            if ($status === 200 && $tx === 'ACTC') {
                $out = [
                    'http_status' => 200,
                    'ok' => true,
                    'data' => [
                        'paymentOwnerName' => $j['paymentOwnerName'] ?? null,
                        'subMerchantName'  => $j['subMerchantName']  ?? null,
                    ],
                ];
                \Log::info('sibs.getnames.success', ['rid' => request()->header('X-Request-Id')]);
                return $out;
            }

            $msg = $this->firstTpp($j) ?? 'Pedido rejeitado ou inválido.';
            \Log::notice('sibs.getnames.reject', [
                'status' => $status, 'tx' => $tx, 'message' => $msg, 'body' => $resp['body'],
                'rid' => request()->header('X-Request-Id')
            ]);

            return $this->err('RJCT', $msg, $status ?: 400);

        }catch(\Exception $e) {
            \Log::error($e->getMessage());
            return $this->err('INETRNAL_ERROR','Falha de comunicação.',503);
        }
      
    }

    private function msgId14(): string { return substr(strtoupper(bin2hex(random_bytes(8))), 0, 14); }
    private function firstTpp(array $j): ?string { return $j['tppMessages'][0]['text'] ?? null; }
    private function err(string $code, string $msg, int $http): array { return ['http_status'=>$http, 'ok'=>false, 'code'=>$code, 'error'=>$msg]; }
}
