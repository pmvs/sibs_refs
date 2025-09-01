<?php
// app/Http/Controllers/OwnerNamesController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SibsGetNamesService;

class OwnerNamesController extends Controller
{
    public function __construct(private SibsGetNamesService $svc) {}

    public function __invoke(Request $r)
    {
        $data = $r->validate([
            'entity'    => ['required','regex:/^\d+$/','max:5'],
            'reference' => ['required','regex:/^\d+$/','max:15'],
        ]);

        try {
            $res = $this->svc->getNames($data['entity'], $data['reference']);
            return response()->json($res, $res['http_status'] ?? 200);
        } catch (\Throwable $e) {
            Log::error('apiLocal.unhandled', [
                'msg' => $e->getMessage(), 'trace' => substr($e->getTraceAsString(),0,800),
                'rid' => $r->header('X-Request-Id')
            ]);
            return response()->json(['ok'=>false,'code'=>'INTERNAL','error'=>'Erro interno.'], 500);
        }
    }
}

