<?php

// use Illuminate\Http\Request;
// use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
//use Exception;
use Illuminate\Support\Facades\Log;



function random_str(
    $length,
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+#$!@?_'
    ) 
{
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
}

function convert_from_latin1_to_utf8_recursively($dat)
{
    if (is_string($dat)) {
        return utf8_encode($dat);
    } elseif (is_array($dat)) {
        $ret = [];
        foreach ($dat as $i => $d) {
            $ret[$i] = convert_from_latin1_to_utf8_recursively($d);
        }

        return $ret;
    } elseif (is_object($dat)) {
        foreach ($dat as $i => $d) {
            $dat->$i = convert_from_latin1_to_utf8_recursively($d);
        }

        return $dat;
    } else {
        return $dat;
    }
}

function invokeAPiAndSaveList( $path, $tipolista)
{
    \Log::channel('listas')->info( 'invokeAPiAndSaveList: ' . $tipolista . ' -> ' . $path);
    try {

        ini_set('max_execution_time',0);
        ini_set('memory_limit', '-1');

        if ( $tipolista == 'OFAC-CONSOLIDADA' ) {
            $tipolista = 'ofacconsolidada';
        }
        if ( $tipolista == 'OFAC-SDN' ) {
            $tipolista = 'ofac';
        }
        if ( $tipolista == 'OFAC-SDN-ADVANCED' ) {
            $tipolista = 'ofacadvanced';
        }

        $storageDir = storage_path() .DIRECTORY_SEPARATOR .'app' .DIRECTORY_SEPARATOR . 'listas'  .DIRECTORY_SEPARATOR . $tipolista  .DIRECTORY_SEPARATOR;

        $storageDir .=  $tipolista . date('Ymd') . '.xml';
    
        \Log::channel('listas')->info( 'nome: ' . $storageDir);

        $fp = fopen($storageDir, 'w+');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$path);
        curl_setopt($ch, CURLOPT_FAILONERROR,1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1) ;

        if ( env('USA_PROXY') == 'S') {
            $proxyip= env('PROXY_IP');
            $proxyport = env('PROXY_PORT');
            curl_setopt($ch, CURLOPT_PROXY, $proxyip . ':' . $proxyport );
        }

        //curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        //curl_setopt($ch, CURLOPT_PROXY, $proxyip . ':' . $proxyport);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        $retValue = curl_exec($ch);  

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        \Log::channel('listas')->info( 'RETURN CODE : ' . $httpcode);
    
        $err = curl_error($ch); //if you need
        if ( $err ) {
            \Log::channel('listas')->error( 'ERROR : ' . $err);
        }
        
        curl_close($ch);

        fclose($fp);

        $retValue = file_get_contents($storageDir);

        return $retValue;
        
    } catch( Exception $e) {
        \Log::channel('listas')->error(__FILE__. ' ' . __LINE__ . ' ' . $e->getMessage());
        return '';
    }
}

function invokeDJAPi( $url, $tipolista, $headers, $payload, $method)
{

    try {



        ini_set('max_execution_time',0);
        ini_set('memory_limit', '-1');

        $proxyip= env('PROXY_IP');
        $proxyport = env('PROXY_PORT');

        $ch = curl_init();

        Log::info('vou chamar o url ' . $url );
        //Log::info('com o body ' . $payload );

        curl_setopt($ch, CURLOPT_URL, $url ) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_ENCODING, '' );
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt($ch, CURLOPT_TIMEOUT, 0 );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1) ;
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method) ;

        curl_setopt($ch, CURLOPT_FAILONERROR, 1 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt($ch, CURLOPT_PROXY, $proxyip . ':' . $proxyport );

        if ( ! empty($headers) ) {
            Log::info('vou fazer set do header ' . print_r($headers, true) );
            if(!is_array($headers)) throw new InvalidArgumentException('headers must be an array');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ( ! empty($payload) ) {
            //Log::info('vou fazer set do body ' . $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, '' . $payload . '');
        }

      //  curl_setopt($ch, CURLOPT_FILE, $fp);
        
    //   curl_setopt_array($ch, array(
    //     CURLOPT_URL => $this->linkainvocar,
    //     CURLOPT_PROXY => $proxyip . ':' . $proxyport ,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_SSL_VERIFYPEER => false,
    //     CURLOPT_ENCODING => "",
    //     CURLOPT_TIMEOUT => 30000,
    //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //     CURLOPT_CUSTOMREQUEST => "GET",
    //     CURLOPT_HTTPHEADER => array(
    //         'Content-Type: application/json',
    //         'aspsp-transaction-id: '  . $pedidorandomid1. ' ',
    //         'ASPSP-Request-ID:'  . $pedidorandomid2 . ' ',
    //         'x-ibm-client-id: ' . $this->clientid . ' ',
    //         'Date: ' . $datapedido . ' ',
    //         'TPP-Certificate: AA==',
    //     ),
    // ));


        $retValue = curl_exec($ch);  

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        Log::info( 'RETURN CODE : ' . $httpcode);
    
        $err = curl_error($ch); //if you need
        if ( $err ) {
            Log::error( 'ERROR : ' . $err);
        }
        
        $resposta = json_decode($retValue, true);

        Log::info("<pre>" . print_r($resposta, true) . "</pre>");

        curl_close($ch);

        // fclose($fp);

        // $retValue = file_get_contents($nome);

        return $resposta;
        
    } catch( Exception $e) {
        Log::error($e->getMessage());
        return '';
    }
}

function executeStatement( $sqlStatement , $database )
{
    try{
        DB::connection($database)->statement($sqlStatement);
    }catch (\Illuminate\Database\QueryException $e){
        Log::error( 'executeStatement Functions QueryException ERROR  : ' . $e->getMessage());
        throw $e;
    }catch (Exception $e){
        Log::error( 'executeStatement Functions ERROR : ' . $e->getMessage());
        throw $e;
    }
}

function incrementaOperacaoWithErrors($tipo, $codigoerrogba = '', $codigoerrosibs = '' ) 
{

  $dt = date('Y-m-d');
  $origem = 'OBA';

  try {
      
      $existe =  StatOper::where('tp_oper', $tipo)->where('data', $dt)->where('origem', $origem)->count();

      if ( $existe == 1) {

          //incrementa
          StatOper::where('tp_oper', $tipo)->where('data', $dt)->where('origem', $origem)->increment('nregs');

      } else {

          $ano = date('Y');
          $mes = date('m');

          //cria registo
          $inputs2 = [
              'tp_oper' => $tipo, 
              'data'     =>  $dt,
              'nregs'   => 1,
              'origem' => $origem,
              'ano'     =>  $ano,
              'mes'   => $mes,
              'codigo_erro_gba'   => $codigoerrogba,
              'codigo_erro_sibs'   => $codigoerrosibs,
          ];
          StatOper::create($inputs2);

      }

  } catch (Exception $e) {
      Log::error($e->getMessage());
  }

}

function getCodigoBanco($sigla)
{
    try {
        $bparametrosrepository = new BParametrosRepository();
        $parametros = $bparametrosrepository->getParametros();
        if ( $parametros ) 
        {
            if ( count($parametros) == 1 ) {
                return $parametros[0]['codigobanco'];
            }
        } 
        else 
        {
            $cdbanco = '0000';
            //caso nao consiga ir ao bparametros
            switch( $sigla ) {
                case 'TVD':
                    $cdbanco = '5340';
                    break;
                case 'MAF':
                    $cdbanco = '5200';
                    break;
                case 'BOM':
                    $cdbanco = '0098';
                    break;
                case 'CHM':
                    $cdbanco = '0097';
                    break;
                default:
                    $cdbanco = '9999';
                    break;
                }
        }
        return $cdbanco;

    }catch(\Exception $e) {
        \Log::channel('daily')->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        return '9999';
    }
 
}

function writeToLog( $data, $contrato, $error) 
{

  try {

      $ano = date("Ym");
      $dia = date("d");

      if (!file_exists('C:\\obapi')) {
          mkdir('C:\\obapi');
      }
      if (!file_exists('C:\\obapi\\logs')) {
          mkdir('C:\\obapi\\logs');
      }
      if (!file_exists('C:\\obapi\\logs\\'.$ano)) {
          mkdir('C:\\obapi\\logs\\'.$ano);
      }
      if (!file_exists('C:\\obapi\\logs\\'.$ano)) {
          mkdir('C:\\obapi\\logs\\'.$ano);
      }
      if (!file_exists('C:\\obapi\\logs\\'.$ano .'\\' . $dia)) {
          mkdir('C:\\obapi\\logs\\'.$ano .'\\' . $dia);
      }

      $path = 'C:\\obapi\\logs\\'.$ano .'\\' . $dia .'\\';

      $filename = $path .  $contrato .  '.log';

      $ip = getUserIpAddr();

      $data = '[' .  $ip .  '] [' . session()->get('_token') . '] [' . date("Y-m-d H:i:s.B") . '] ' . $data .  PHP_EOL ;

      if ( $error ) {
          file_put_contents($filename . '.ERROR.log', $data, FILE_APPEND );
      } else {
          file_put_contents($filename , $data, FILE_APPEND );
      }    
      
  } catch (Exception $e) {
      Log::error($e->getMessage());
  }
  
}

function writeToLogDir( $dir , $nmdir,  $texto, $titulo, $error) 
{

  try {

    $ano = date("Ym");
    $dia = date("d");

    if (!file_exists('C:\\' . $dir )) {
    mkdir('C:\\'.  $dir );
    }
    if (!file_exists('C:\\'. $dir .'\\' . $nmdir)) {
        mkdir('C:\\'. $dir .'\\' . $nmdir);
    }
    if (!file_exists('C:\\'. $dir .'\\'. $nmdir.'\\logs')) {
        mkdir('C:\\'. $dir .'\\'. $nmdir.'\\logs');
    }
    if (!file_exists('C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano)) {
        mkdir('C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano);
    }
    if (!file_exists('C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano)) {
        mkdir('C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano);
    }
    if (!file_exists('C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano .'\\' . $dia)) {
        mkdir('C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano .'\\' . $dia);
    }

    $path2 = 'C:\\'. $dir .'\\'. $nmdir.'\\logs\\'.$ano .'\\' . $dia .'\\';

    $path = config('app.path_desbloqueio_conta');

    $filename = $path .  $titulo .  '.log';
    $filename2 = $path2 .  $titulo .  '.log';

    $ip = getUserIpAddr();

    $data = '[' .  $ip .  '] [' . session()->get('_token') . '] [' . date("Y-m-d H:i:s.B") . '] ' . $texto .  PHP_EOL ;

    if ( $error ) {
        file_put_contents($filename . '.ERROR.log', $texto, FILE_APPEND );
        file_put_contents($filename2 . '.ERROR.log', $texto, FILE_APPEND );
    } else {
        file_put_contents($filename , $data, FILE_APPEND );
        file_put_contents($filename2 , $data, FILE_APPEND );
    }    
      
  } catch (Exception $e) {
      Log::error($e->getMessage());
  }
  
}

function getUserIpAddr()
{
  $ipaddress = '';
  if (isset($_SERVER['HTTP_CLIENT_IP']))
      $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
  else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
      $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
  else if(isset($_SERVER['HTTP_X_FORWARDED']))
      $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
  else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
      $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
  else if(isset($_SERVER['HTTP_FORWARDED']))
      $ipaddress = $_SERVER['HTTP_FORWARDED'];
  else if(isset($_SERVER['REMOTE_ADDR']))
      $ipaddress = $_SERVER['REMOTE_ADDR'];
  else
      $ipaddress = 'UNKNOWN';    
  return $ipaddress;
}

function calculateTransactionDuration($startDate, $endDate)
{
    $startDateFormat = new DateTime($startDate);
    $EndDateFormat = new DateTime($endDate);
    // the difference through one million to get micro seconds
    $uDiff = ($startDateFormat->format('u') - $EndDateFormat->format('u')) / (1000 * 1000);
    $diff = $startDateFormat->diff($EndDateFormat);
    $s = (int) $diff->format('%s') - $uDiff;
    $i = (int) ($diff->format('%i')) * 60; // convert minutes into seconds
    $h = (int) ($diff->format('%h')) * 60 * 60; // convert hours into seconds

    return sprintf('%.6f', abs($h + $i + $s)); // return total duration in seconds
}

function remove_4_byte($string) {
    $char_array = preg_split('/(?<!^)(?!$)/u', $string );
    for($x=0;$x<sizeof($char_array);$x++) {
        if(strlen($char_array[$x])>3) {
            $char_array[$x] = "";
        }
    }
    return implode($char_array, "");
}

function stripAccents($str) 
{
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function remove_accents($string) 
{
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

    $string = strtr($string, $chars);

    return $string;
}

function ccMasking($number, $maskingCharacter = '*') 
{

    $lennumber = strlen($number);
    $xnumbers = $lennumber - 10;

    return substr($number, 0, 6) . str_repeat($maskingCharacter, $xnumbers) . substr($number, -4);



}

function binarySearch($array, $value) 
{
    
    // Set the left pointer to 0.
    $left = 0;
    // Set the right pointer to the length of the array -1.
    $right = count($array) - 1;

    while ($left <= $right) {
      // Set the initial midpoint to the rounded down value of half the length of the array.
      $midpoint = (int) floor(($left + $right) / 2);

      if ($array[$midpoint] < $value) {
        // The midpoint value is less than the value.
        $left = $midpoint + 1;
      } elseif ($array[$midpoint] > $value) {
        // The midpoint value is greater than the value.
        $right = $midpoint - 1;
      } else {
        // This is the key we are looking for.
        return $midpoint;
      }
    }
    // The value was not found.
    return NULL;
}
 
function binarySearchBool(Array $arr, $x)
{
    // check for empty array
    if (count($arr) === 0) return false;
    $low = 0;
    $high = count($arr) - 1;
      
    while ($low <= $high) {
          
        // compute middle index
        $mid = floor(($low + $high) / 2);
   
        // element found at mid
        if($arr[$mid] == $x) {
            return true;
        }
  
        if ($x < $arr[$mid]) {
            // search the left side of the array
            $high = $mid -1;
        }
        else {
            // search the right side of the array
            $low = $mid + 1;
        }
    }
      
    // If we reach here element x doesnt exist
    return false;
}
  
function binarySearchComp($needle, array $haystack, $compare, $high, $low = 0, $containsDuplicates = false)
{
    $key = false;
    // Whilst we have a range. If not, then that match was not found.
    while ($high >= $low) {
        // Find the middle of the range.
        $mid = (int)floor(($high + $low) / 2);
        // Compare the middle of the range with the needle. This should return <0 if it's in the first part of the range,
        // or >0 if it's in the second part of the range. It will return 0 if there is a match.
        $cmp = call_user_func($compare, $needle, $haystack[$mid]);
        // Adjust the range based on the above logic, so the next loop iteration will use the narrowed range
        if ($cmp < 0) {
            $high = $mid - 1;
        } elseif ($cmp > 0) {
            $low = $mid + 1;
        } else {
            // We've found a match
            if ($containsDuplicates) {
                // Find the first item, if there is a possibility our data set contains duplicates by comparing the
                // previous item with the current item ($mid).
                while ($mid > 0 && call_user_func($compare, $haystack[($mid - 1)], $haystack[$mid]) === 0) {
                    $mid--;
                }
            }
            $key = $mid;
            break;
        }
    }
 
    return $key;
}

function is_decimal( $val )
{
    return is_numeric( $val ) && floor( $val ) != $val;
}

function sql($a,$odbc,$user,$pass,$sql,$tipo)
{


    //   dd('SQL1:' .  $odbc." - ".$pass." - ".$sql." - ".$tipo);
    //    Log::info('SQL1:' .  $odbc." - ".$pass." - ".$sql." - ".$tipo);


    $caller1=array_shift($a);
    $bt = debug_backtrace(0);
    $caller = array_shift($bt);
    $campo = [];

    $conn = odbc_connect($odbc,$user,$pass);
 
    if (!$conn)
    {                                    
        $Data = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn); 
        Log::info($data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn)); 
        odbc_close($conn);
    }

    $tipo = strtoupper($tipo);
    
    if($odbc=='odbc_unix')
    {
        $sql_alt="SET EXPLAIN ON; SET ISOLATION TO DIRTY READ;";
    //        $sql_alt="SET ISOLATION TO DIRTY READ;";
        $rs = odbc_exec($conn,$sql_alt);
    }           
   
    if($tipo=="SELECT")
    {
        $res = odbc_prepare($conn, $sql);

      //  Log::info('res:' .  $res);

        if(!$res) 
        {
            $Data = odbc_errormsg($res)." - ".sql_erro_de_comunicacao_com_servidor; 
            $data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn)." - ".sql_erro_de_comunicacao_com_servidor; 
        }
        
        $rs = odbc_exec($conn,$sql);   
        
      //  Log::info('res2:' .  $rs);

        if(!$rs)
        {
            if (odbc_error())
            {
                $Data = odbc_errormsg($conn)."\n"; 
            }
            odbc_close($conn);
            $data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn); 
        }
       
       
        $i=1;
        while (odbc_fetch_row($rs))
        {
            for($j=1;$j<=odbc_num_fields($rs);$j++)
            {
                $nome_campo=odbc_field_name($rs,$j);
                $campo[$i][$nome_campo]=odbc_result($rs,$j);
            }
            $i++;
        }
        $i--;
    }
    elseif($tipo=="SELECT1")
    {
        $res = odbc_prepare($conn, $sql);
        

        if(!$res) 
        {
            $Data = odbc_errormsg($res)." - ".sql_erro_de_comunicacao_com_servidor; 
            $data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn)." - ".sql_erro_de_comunicacao_com_servidor; 
        }
        
          
        $rs = odbc_exec($conn,$sql);      
    //echo "<br>Numero de linhas: ".odbc_errormsg($conn);
        if(!$rs)
        {
            if (odbc_error())
            {
                $Data = odbc_errormsg($conn)."\n"; 
            }
            odbc_close($conn);
            $data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn); 
        }
       
                          
        $i=1;
        while (odbc_fetch_row($rs))
        {
            
            for($j=1;$j<=odbc_num_fields($rs);$j++)
            {
                $nome_campo=odbc_field_name($rs,$j);
                $campo[$i][$nome_campo]=odbc_result($rs,$j);
    
            }
            $i++;
        }
        $i--;
       
        
    }
    
    elseif($tipo=="UPDATE" | $tipo=="INSERT" | $tipo=="DELETE")
    {

        $rs = odbc_exec($conn,$sql);   
        $campo=odbc_errormsg($conn);

        if(!$rs)
        {
            $Data = "UPDATE / INSERT / DELETE\n".odbc_errormsg($conn)."\n\r"; 
            $data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn); 
            
            print_r(odbc_error($rs));
            echo "<hr>";
            print_r(odbc_errormsg($conn));
            
            odbc_close($conn);
        }
        $i=999;
    }
    elseif($tipo=="CREATE" || $tipo=="DROP")
    {
        $rs = odbc_exec($conn,$sql);   
        $campo=odbc_errormsg($conn);

        if(!$rs)
        {
            $Data = "UPDATE / INSERT / DELETE\n".odbc_errormsg($conn)."\n\r"; 
            $data1 = odbc_error()." - ".odbc_error($conn)." - ".odbc_errormsg($conn); 
            odbc_close($conn);
        }
        $i=999;
    }

    odbc_close($conn);
    $resultado[0]=$i;
    $resultado[1]=$campo;

    //Log::info('resultado:' .  print_r($resultado,true));

    return $resultado;

}    

function seems_utf8($str) 
{
        mbstring_binary_safe_encoding();
        $length = strlen($str);
        reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
                $c = ord($str[$i]);
                if ($c < 0x80) $n = 0; # 0bbbbbbb
                elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
                elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
                elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
                elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
                elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
                else return false; # Does not match any model
                for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                        if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                                return false;
                }
        }
        return true;
}

function remove_accents2022($string) 
{

        if ( !preg_match('/[\x80-\xff]/', $string) )
                return $string;

    	if (seems_utf8($string)) {
    	    
            $chars = array(
    	                // Decompositions for Latin-1 Supplement	                chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
    	                chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    	                chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    	                chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    	                chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
    	                chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
    	                chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
    	                chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
    	                chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
    	                chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
    	                chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    	                chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    	                chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    	                chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    	                chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    	                chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
    	                chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
    	                chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
    	                chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
    	                chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
    	                chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    	                chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    	                chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    	                chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    	                chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
    	                chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
    	                chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
    	                chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
    	                chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
    	                chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
    	                chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
    	                chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
    	                // Decompositions for Latin Extended-A
    	                chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    	                chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    	                chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    	                chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    	                chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    	                chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    	                chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    	                chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    	                chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    	                chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    	                chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    	                chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    	                chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    	                chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    	                chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    	                chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    	                chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    	                chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    	                chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    	                chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    	                chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    	                chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    	                chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    	                chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    	                chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    	                chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    	                chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    	                chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    	                chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    	                chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    	                chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    	                chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    	                chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    	                chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    	                chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    	                chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    	                chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    	                chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    	                chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    	                chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    	                chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    	                chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    	                chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    	                chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    	                chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    	                chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    	                chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    	                chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    	                chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    	                chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    	                chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    	                chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    	                chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    	                chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    	                chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    	                chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    	                chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    	                chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    	                chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    	                chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    	                chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    	                chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    	                chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    	                chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
    	                // Decompositions for Latin Extended-B
    	                chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
    	                chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
    	                // Euro Sign
    	                chr(226).chr(130).chr(172) => 'E',
    	                // GBP (Pound) Sign
    	                chr(194).chr(163) => '',
    	                // Vowels with diacritic (Vietnamese)
                        // unmarked
    	                chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
    	                chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
    	                // grave accent
    	                chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
    	                chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
    	                chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
    	                chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
    	                chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
    	                chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
    	                chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
    	                // hook
    	                chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
    	                chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
    	                chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
    	                chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
    	                chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
    	                chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
    	                chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
    	                chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
    	                chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
    	                chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
    	                chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
    	                chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
    	                // tilde
    	                chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
    	                chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
    	                chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
    	                chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
    	                chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
    	                chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
    	                chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
    	                chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
    	                // acute accent
    	                chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
    	                chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
    	                chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
    	                chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
    	                chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
    	                chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
    	                // dot below
    	                chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
    	                chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
    	                chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
    	                chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
    	                chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
    	                chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
    	                chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
    	                chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
    	                chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
    	                chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
    	                chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
    	                chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
    	                // Vowels with diacritic (Chinese, Hanyu Pinyin)
    	                chr(201).chr(145) => 'a',
    	                // macron
    	                chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
    	                // acute accent
    	                chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
    	                // caron
    	                chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
    	                chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
    	                chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
    	                chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
    	                chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
    	                // grave accent
    	                chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
    	                );
    	
    	                // Used for locale-specific rules
    	                $locale = get_locale();
    	
    	                if ( 'de_DE' == $locale ) {
    	                        $chars[ chr(195).chr(132) ] = 'Ae';
    	                        $chars[ chr(195).chr(164) ] = 'ae';
    	                        $chars[ chr(195).chr(150) ] = 'Oe';
    	                        $chars[ chr(195).chr(182) ] = 'oe';
    	                        $chars[ chr(195).chr(156) ] = 'Ue';
    	                        $chars[ chr(195).chr(188) ] = 'ue';
    	                        $chars[ chr(195).chr(159) ] = 'ss';
    	                } elseif ( 'da_DK' === $locale ) {
    	                        $chars[ chr(195).chr(134) ] = 'Ae';
    	                        $chars[ chr(195).chr(166) ] = 'ae';
    	                        $chars[ chr(195).chr(152) ] = 'Oe';
    	                        $chars[ chr(195).chr(184) ] = 'oe';
    	                        $chars[ chr(195).chr(133) ] = 'Aa';
    	                        $chars[ chr(195).chr(165) ] = 'aa';
    	                }
    	
    	                $string = strtr($string, $chars);
    	        }
                else 
                {
    	                // Assume ISO-8859-1 if not UTF-8
    	                $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
    	                        .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
    	                        .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
    	                        .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
    	                        .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
    	                        .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
    	                        .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
    	                        .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
    	                        .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
    	                        .chr(252).chr(253).chr(255);
    	
    	                $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
    	
    	                $string = strtr($string, $chars['in'], $chars['out']);
    	                $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
    	                $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
    	                $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    	        }
       
    	    return $string;
   
}

function string2ByteArray($string) 
{
    return unpack('C*', $string);
}
  
function byteArray2String($byteArray) 
{
    $chars = array_map("chr", $byteArray);
    return join($chars);
}

function byteArray2Hex($byteArray) 
{
    $chars = array_map("chr", $byteArray);
    $bin = join($chars);
    return bin2hex($bin);
}
  
function hex2ByteArray($hexString) 
{
    $string = hex2bin($hexString);
    return unpack('C*', $string);
}
  
function string2Hex($string) 
{
    return bin2hex($string);
}
  
function hex2String($hexString) 
{
    return hex2bin($hexString);
}
  
    
