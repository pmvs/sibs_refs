<?php

namespace App\Models\Gba;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;

class DestinatariosFrequentes extends Model
{

    protected $connection = 'mysql-prod';
    protected $table = 'trf_freq';
    protected $primaryKey = 'id_trf_freq';
    public $timestamps = false;



}