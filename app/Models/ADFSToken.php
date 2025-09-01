<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

class ADFSToken extends Model
{

    protected $connection = "mysql";
    protected $logchannel = 'proxylookup';
    protected $isConnProduction = false;
    protected $table = "bp_adfs_tokens";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
         'dt_pedido',
         'resource', 
         'client_id', 
         'client_assertion', 
         'access_token', 
         'token_type',
         'expires_in',
         'ativo',
         'audience',
         'valid_until'
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
        \Log::channel($this->logchannel)->info('__construct ADFSToken');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->isConnProduction );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 ADFSToken');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_2 ADFSToken');
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