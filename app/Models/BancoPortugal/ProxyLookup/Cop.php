<?php

namespace App\Models\BancoPortugal\ProxyLookup;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;

class Cop extends Model
{
    protected $connection = 'mysql';
    protected $table = 'cop';
    public $timestamps = false;
    private $logchannel = 'bancoportugal';
    private $isConnProduction = true;
    private $mensagemErro = '';

}