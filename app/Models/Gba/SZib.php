<?php

namespace App\Models\Gba;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;

class SZib extends Model
{

    protected $connection = 'odbc-gba';
    protected $table = 'szib';
    public $timestamps = false;


    public static function getBIC($cdbanco)
    {
        $sql="select bic from szib where n_zib ='$cdbanco'";
    
        $devolve = DB::connection('odbc-gba')->select($sql);    

        if($devolve) {
            if(count($devolve) == 1) {
                return $devolve[0]->bic;
            }
        }

        return "";

    }

    public static function getInfo( $cdbanco)
    {
        try {
         $sql="select * from szib where n_zib='$cdbanco' ";
    
        $devolve = DB::connection('odbc-gba')->select($sql);    

        return $devolve;
        }catch(\Exception $e){
            \Log::error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
       
       

    }


}