<?php

namespace App\Models\Gba;

use Illuminate\Database\Eloquent\Model;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SPais extends Model
{

    protected $connection = 'odbc-gba';
    protected $table = 'spais';
    public $timestamps = false;


    public static function getNomePais($a2)
    {
        $sql="select nm_pais from spais where a2='$a2'";
    
        try {
            $devolve = DB::connection('odbc-gba')->select($sql);    
         //   throw new Exception ('Estou me baixo e tenho que vir acima!');
        } catch ( Exception $e) {
            
            \Log::error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            throw $e;
        }

        return $devolve;

    }

    public static function getInfoPais($a2, $logchannel)
    {
        $sql="select * from spais where a2='$a2'";
        
        try {
            $devolve = DB::connection('odbc-gba')->select($sql);  
        } catch ( Exception $e) {
            \Log::channel($logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            return [];
            throw $e;
        }
          
        return $devolve;

    }

}