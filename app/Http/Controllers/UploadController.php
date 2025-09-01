<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\UploadFich;

use Log;
use Validator;
use Session;
use DB;
use FORM;


class UploadController extends Controller
{

    private $logchannel = 'uploads';

    public function __construct() {
        $this->logchannel = 'uploads';
        \Log::channel($this->logchannel)->info( 'UploadController __construct ');
    }

    public function index() {
        $this->logchannel = 'uploads';
        \Log::channel($this->logchannel)->info( 'UploadController index ');
        print_r($_POST);
        print_r($_FILES);
    }

    public function getUploadFile(Request $request) {

        try {

            \Log::channel($this->logchannel)->info( 'UploadController getUploadFile ' . print_r($request->all(), true) );
            \Log::channel($this->logchannel)->info( 'UploadController POST :' . print_r($_POST, true) );
            \Log::channel($this->logchannel)->info( 'UploadController FILES :' . print_r($_FILES, true) );
    
            ini_set('post_max_size', '64M');
            ini_set('upload_max_filesize', '64M');
    
            \Log::channel($this->logchannel)->info( 'UploadController ini_set post_max_size 64M');
            \Log::channel($this->logchannel)->info( 'UploadController ini_set upload_max_filesize 64M ');

            \Log::channel($this->logchannel)->info( 'UploadController START UPLOAD process');
    
            $upFile = new UploadFich();
    
            $maquina_origem=$_SERVER['REMOTE_ADDR'];
            $nome_tabela=$_POST['nome_tabela'];
            $fich_nome     = $_FILES['ficheiro']['name'];
            $fich_extencao = pathinfo($fich_nome, PATHINFO_EXTENSION);
            $fich_type     = $_FILES['ficheiro']['type'];
            $fich_tmp_name = $_FILES['ficheiro']['tmp_name'];
            $fich_error    = $_FILES['ficheiro']['error'];
            $fich_size     = $_FILES['ficheiro']['size'];
            $ficheiro = file_get_contents($fich_tmp_name);
            $post=$_POST;
            $files=$_FILES;
    
            $upFile_1 = $upFile->upLoadFiles($files,$post);
    
            \Log::channel($this->logchannel)->info( 'UploadController UPLOAD TERMINATED with success'  );
    
            return true;

        }catch(\Exception $e) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
            \Log::channel($this->logchannel)->error( 'UploadController UPLOAD TERMINATED with ERROR'  );
            return false;
        }

      
     }



}
