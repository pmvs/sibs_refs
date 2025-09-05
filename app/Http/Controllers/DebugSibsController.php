<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SibsGetNamesService;

class DebugSibsController extends Controller
{
    public function form()
    {
        \Log::info('---View form SIBS----');
        
        return view('debug.sibs-form');
    }

    public function submit(Request $request, SibsGetNamesService $svc)
    {
        \Log::info('---Post form SIBS----');

        $data = $request->validate([
            'entity'    => ['required','regex:/^\d+$/','max:5'],
            'reference' => ['required','regex:/^\d+$/','max:15'],
        ]);

        $res = $svc->getNames($data['entity'], $data['reference']);

        return view('debug.sibs-result', [
            'input'  => $data,
            'result' => $res,
        ]);
    }
}
