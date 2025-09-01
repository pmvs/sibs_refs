<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SibsGetNamesService
{
    public function __construct(private CurlClient $http, private SibsAffinity $aff) {}

    public function getNames(string $entity, string $reference): array
    {
        $base = $this->aff->apply(rtrim(config('net.sibs_base'), '/'));
        $ver  = config('net.sibs_ver','v1');
        $url  = sprintf('%s/sibs/%s/payment-owners/%s/%s', $base, $ver, $entity, $reference);

        $headers = [
            'Accept'          => 'application/json',
            'x-ibm-client-id' => config('net.sibs_id'),
            'X-Message-ID'    => $this->msgId14(),
        ];
        if ($b = config('net.bearer')) {
            $headers['Authorization'] = 'Bearer '.$b; // normalmente não precisa nesta operação
        }

        Log::info('sibs.getnames.request', [
            'entity' => $entity, 'reference' => $reference,
            'url' => $url, 'rid' => request()->header('X-Request-Id')
        ]);

        $resp = $this->http->request('GET', $url, ['headers' => $headers, 'retries' => 2]);

        $this->aff->rememberFrom($resp['headers']);

        if ($resp['error']) {
            Log::warning('sibs.getnames.network_error', ['error' => $resp['error'], 'rid' => request()->header('X-Request-Id')]);
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
            Log::info('sibs.getnames.success', ['rid' => request()->header('X-Request-Id')]);
            return $out;
        }

        $msg = $this->firstTpp($j) ?? 'Pedido rejeitado ou inválido.';
        Log::notice('sibs.getnames.reject', [
            'status' => $status, 'tx' => $tx, 'message' => $msg, 'body' => $resp['body'],
            'rid' => request()->header('X-Request-Id')
        ]);
        return $this->err('RJCT', $msg, $status ?: 400);
    }

    private function msgId14(): string { return substr(strtoupper(bin2hex(random_bytes(8))), 0, 14); }
    private function firstTpp(array $j): ?string { return $j['tppMessages'][0]['text'] ?? null; }
    private function err(string $code, string $msg, int $http): array { return ['http_status'=>$http, 'ok'=>false, 'code'=>$code, 'error'=>$msg]; }
}
