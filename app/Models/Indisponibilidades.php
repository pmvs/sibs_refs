<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

class Indisponibilidades extends Model
{

    protected $logchannel = 'testes';
   

    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */
    public function __construct() 
    {
        $this->logchannel = 'testes';
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct CopPayload');
       
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 CopPayload');
    }

  

    /*
    |--------------------------------------------------------------------------
    | SETTERS
    |--------------------------------------------------------------------------
    */

   

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */

    public function executeTest()
    {
        
    }

    
}