<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;
use Log;
use Debugbar;

class UploadFich extends Model
{

    private $logchannel = 'uploads';


    public  function  UpLoadFiles($files,$post)
    {

        $this->logchannel = 'uploads';
        \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles ');

        try {

            $tabela=$post['nome_tabela'];
            $ficheiro_nm=$files['ficheiro']['name'];
            $ficheiro_type=$files['ficheiro']['type'];
            $ficheiro_tmp_name=$files['ficheiro']['tmp_name'];
            $ficheiro_error=$files['ficheiro']['error'];
            $ficheiro_size=$files['ficheiro']['size'];

            $newphrase = str_replace('\\','\\\\',$ficheiro_tmp_name);
            
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : BEGIN transaction ');
            // Begin Transaction
            DB::connection('mysql')->beginTransaction();

            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : set global ini ');
            $sql="set global local_infile=1;";
            \Log::channel($this->logchannel)->info( $sql );
            $devolve = DB::connection('mysql')->insert($sql);
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : global ini OK ');
          
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : delete cop ');
            $sql="DELETE FROM COP;";
            \Log::channel($this->logchannel)->info( $sql );
            $devolve = DB::connection('mysql')->insert($sql);
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : delete cop OK ');
          
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : load data');
            $sql="LOAD DATA LOCAL INFILE '".$newphrase."' INTO TABLE cop FIELDS TERMINATED BY '|' ;";
            \Log::channel($this->logchannel)->info( $sql );
        	$devolve = DB::connection('mysql')->insert($sql);
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : load data OK');

            // Commit Transaction
            DB::connection('mysql')->commit();
            \Log::channel($this->logchannel)->info( 'UploadFich.UpLoadFiles : COMMIT transaction ');

            return true;

        } catch(\Illuminate\Database\QueryException $e) {

            // Rollback Transaction
            DB::connection('mysql')->rollback();
            \Log::channel($this->logchannel)->error( 'UploadFich.UpLoadFiles : ROLLBACK transaction ');

            echo "AQUI_1: ".__FILE__."-".__LINE__;

            $msgerro = $e->getMessage();
            \Log::channel($this->logchannel)->error('UploadFich QueryException  '. $msgerro );
            return false;

        }  catch (\Exception $e) {

            // Rollback Transaction
            DB::connection('mysql')->rollback();
            \Log::channel($this->logchannel)->error( 'UploadFich.UpLoadFiles : ROLLBACK transaction ');

            echo "AQUI_2: ".__FILE__."-".__LINE__;
            $msgerro = $e->getMessage();
            \Log::channel($this->logchannel)->error( 'UploadFich Exception  '. $msgerro );
            return false;
        }

       // print_r($files)." <hr> ".print_r($post);
        echo "AQUI: ".__FILE__."-".__LINE__;
        exit(0);


        return true;



    }



}
