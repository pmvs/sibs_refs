<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

class BPPLRequest extends Model
{

    protected $connection = "mysql";
    protected $logchannel = 'proxylookup';
    protected $isConnProduction = false;
    protected $table = "bp_pl_requests";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
         'dt_pedido',
         'token_id', 
         'payload', 
         'response', 
         'audience',
         'correlation_id',
         'n_netcaixa',
         'user_id',
         'timeelapsed',
         'http_code_response'
    ];


    
    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */
    public function __construct() 
    {
        $this->logchannel = 'proxylookup';
        $this->isConnProduction = config('app.connection_prod');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct BPRequest');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->isConnProduction );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 BPRequest');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_2 BPRequest');
    }

    /*
    |--------------------------------------------------------------------------
    | SETTERS
    |--------------------------------------------------------------------------
    */

    public function setIsConnectionProd ( $isConnProduction )
    {
        $this->isConnProduction = $isConnProduction;
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */
    
}