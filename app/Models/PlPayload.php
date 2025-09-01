<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

class PlPayload extends Model
{
    protected $connection = "mysql";
    protected $logchannel = 'proxylookup';
    protected $isConnProduction = false;
    protected $table = "bp_pl_payload";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'dt_pedido' ,
        'request_id',
        'n_netcaixa', 
        'user_id', 
        'psp_code', 
        'customer_identifier',
        'customer_identifier_type',
        'fiscal_number',
        'customer_type',
        'iban',
        'correlation_id_origin',
        'timestamp',
        'success',
        'correlation_id',
        'message',
        'errors_codes',
        'errors_values',
        'http_code',
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
        \Log::channel($this->logchannel)->info('__construct PlPayload');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->isConnProduction );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 PlPayload');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_2 PlPayload');
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