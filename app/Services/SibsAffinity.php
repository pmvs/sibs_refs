<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SibsAffinity
{
    private const KEY = 'sibs_affinity_host';

    public function apply(string $base): string
    {
        $host = Cache::get(self::KEY);
        if (!$host) return $base;
        $p = parse_url($base);
        if (!$p || empty($p['scheme'])) return $base;
        return $p['scheme'].'://'.$host;
    }

    public function rememberFrom(array $headers): void
    {
        if (!empty($headers['sibs-affinity-host'])) {
            $host = trim($headers['sibs-affinity-host']);
            Cache::put(self::KEY, $host, now()->addMinutes(30));
            Log::info('sibs.affinity.set', ['host' => $host, 'rid' => request()->header('X-Request-Id')]);
        }
    }
}
