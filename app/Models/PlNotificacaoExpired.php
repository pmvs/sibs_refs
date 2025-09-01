<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

class PlNotificacaoExpired extends Model
{
    protected $connection = "mysql";
    protected $logchannel = 'pl-expired';
    protected $isConnProduction = false;
    protected $table = "psp_notification_expired";
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'dt_pedido' ,
        'request_id',
        'n_netcaixa', 
        'user_id', 
        'correlation_id',
        'correlation_id_origin',
        'customer_identifier',
        'customer_identifier_type',
        'fiscal_number',
        'customer_type',
        'iban',
        'message',
        'status',
        'dt_status'
    ];
  


    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */
    public function __construct() 
    {
        $this->logchannel = 'pl-expired';
        $this->isConnProduction = config('app.connection_prod');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct PlNotificacaoExpired');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->isConnProduction );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 PlNotificacaoExpired');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_2 PlNotificacaoExpired');
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