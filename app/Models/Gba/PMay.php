<?php

namespace App\Models\Gba;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;

class PMay extends Model
{

    protected $connection = 'odbc-gba';
    protected $table = 'pmay';
    public $timestamps = false;


    public static function getInfo($codpais, $cdbanco)
    {
        $sql="select * from pmay where pais='$codpais' and cd_banco = '$cdbanco'";
    
        //\Log::channel('testes')->info($sql);

        $devolve = DB::connection('odbc-gba')->select($sql);    

       // \Log::channel('testes')->info(print_r($devolve, true));

        return $devolve;

    }

}