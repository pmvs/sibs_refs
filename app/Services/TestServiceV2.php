<?php 

namespace App\Services;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Controller;
use App\Services\Testes\Indisponibilidade;
use App\Services\Testes\PL;

class TestServiceV2 extends Controller
{
    private $logchannel = 'testes';
    private $testNumber = 0;
    private $tabPosition =0;
    private $typeOfIndisponibilidade = ['add', 'update', 'list'];
    private $testNamesIndisponibilidades = [
        1 => '2.1 - Criar de uma indisponibilidade programada',
        2 => '2.2 - Atualização da hora esperada de ínicio e fim de uma indisponibilidade programada (Atualizar a indisponibilidade criada no CT 2.1)',
        3 => '2.3 - Atualização da indisponibilidade programada  (Atualizar a indisponibilidade criada no CT 2.1, para o estado finalizada)',
        4 => '2.4.1 - Comunicação de uma indisponibilidade  programada e de seguida cancelar essa indisponibilidade',
        5 => '2.4.2 - Comunicação de uma indisponibilidade  programada e de seguida cancelar essa indisponibilidade',
        6 => '2.5.1 - Comunicação de uma indisponibilidade  programada - Igual a uma previamente comunicada',
        7 => '2.5.2 - Comunicação de uma indisponibilidade  programada - Igual a uma previamente comunicada',
        8 => '2.6 - Criar uma indisponibilidade programada com timestamp de expected start date > expected end date',
        9 => '2.7 - Atualização de uma indisponibilidade  programada - Campo "Unavailability id" = "número inexistente de indisponibilidade"',
        10 => '2.8 - Cancelar uma indisponibilidade  programada com estado final (Status =3), após efetuar CT 2.1, 2.2, 2.3 e 2.4',
        11 => '2.9 - Criar  uma indisponibilidade  programada - Campo "PSP Code" = "número do PSP inválido"',
        12 => '2.10.1 - Criar uma indisponibilidade programada e comunicar a conclusão dessa indisponibilidade (status = 3)*se não for comunicado o Real End Date é assumido o timestamp de comunicação como o timestamp de conclusão da indisponibilidade',
        13 => '2.10.2 - Criar uma indisponibilidade programada e comunicar a conclusão dessa indisponibilidade (status = 3)*se não for comunicado o Real End Date é assumido o timestamp de comunicação como o timestamp de conclusão da indisponibilidade',
        14 => '2.10.3 - Criar uma indisponibilidade programada e comunicar a conclusão dessa indisponibilidade (status = 3)*se não for comunicado o Real End Date é assumido o timestamp de comunicação como o timestamp de conclusão da indisponibilidade',
        15 => '3.1 - Criar de uma indisponibilidade não programada ',
        16 => '3.2 - Comunicação de uma indisponibilidade não  programada - Igual a uma previamente comunicada',
        17 => '3.3 - Finalizar uma indisponibilidade não programada ',
        18 => '3.4 - Finalizar uma indisponibilidade não programada em estado final',
        19 => '3.5 - Criar de uma indisponibilidade não programada com timestamp no Futuro',
        20 => '3.6 - Criar de uma indisponibilidade não programada com timestamp no Passado ',
        21 => '3.7 - Criar de uma indisponibilidade não programada   - Campo "PSP Code" = "número do PSP inválido"',
        22 => '3.8 - Atualizar uma indisponibilidade não programada que não existe',
        23 => '4.1 - Obter Lista de indisponibilidades em progresso',
        24 => '4.2 - Obter Lista de indisponibilidades finalizadas',
        25 => '4.3 - Obter Lista de indisponibilidades total',
        26 => '4.4 - Obter Lista de indisponibilidades - Campo "PSP Code unavailable" = "número do PSP inválido"',
    ];
    private $testNamesPL = [
        1 => '1.1 Associação - Cliente Particular - sem Customer identifier type',
        2 => '1.2 Associação - Cliente Empresa - sem Customer identifier type',
        3 => '1.3 Associação - Cliente Particular NIF com sucesso',
        4 => '1.4 Associação - Cliente Particular NIF - criar associação duplicada',
        5 => '1.5 Associação - Cliente Particular NIF - criar associação com CI diferente do FN',
        6 => '1.6 Associação - Cliente Particular NIF - criar associação com NIF incompleto',
        7 => '1.7 Associação - Cliente Particular - NIF criar associação com NIF inválido',
        8 => '1.8 Associação - Cliente Particular NIF - sem Customer identifier',
        9 => '1.9 Associação - Cliente Particular - criar associação para número de telemóvel com NIF do tipo 45x',
        10 => '1.10 Confirmação da associação PL - Identificador aderente à funcionalidade de PL - NIF',
        11 => '1.11 Consulta lista aderentes com sucesso - NIF',
        12 => '1.12 Consulta IBAN - Cliente Particular - NIF',
        13 => '1.13 Dissociação - Cliente Particular NIF com sucesso',
        14 => '1.14 Dissociação - Cliente Particular NIF  - Associação inexistente',
        15 => '1.15.1 Notificação de dissociação - Cliente Particular - Nova associação no mesmo PSP - Efetuar associacao com telemovel',
        16 => '1.15.2 Notificação de dissociação - Cliente Particular - Nova associação no mesmo PSP - Efetuar uma nova associação para um cliente particular que já tem uma associação ativa, com outro iban',
        17 => '1.16.1 Notificação de dissociação - Cliente Empresa - Nova associação no mesmo PSP | Passo 1',
        18 => '1.16.2 Notificação de dissociação - Cliente Empresa - Nova associação no mesmo PSP | Passo 2',
        19 => '1.17.1 Notificação de dissociação - Cliente Particular NIF - Nova associação no mesmo PSP | Passo 1',
        20 => '1.17.1 Notificação de dissociação - Cliente Particular NIF - Nova associação no mesmo PSP | Passo 2',
        21 => '1.18.1 Notificação de pendente  - Reativar associação antes da dissociação automática | Passo 1 - Efetuar um pedido de associação para um cliente particular, para criar uma associação pendente (IBAN e NIF diferentes da original)',
        22 => '1.18.2 Notificação de pendente  - Reativar associação antes da dissociação automática | Passo 2 - Verificar que foi recebida uma notificação a indicar que a associação original (1ª associação) se encontra no estado pendente.',
        23 => '1.18.3 Notificação de pendente  - Reativar associação antes da dissociação automática | Passo 3 - Reativar a associação que se encontra no estado pendente, enviado um novo pedido de associação para o mesmo identificador (Nº telemóvel)',
        24 => '1.19.1 Notificação de Pendente - Reativar associação depois da dissociação automática - Passo 1 - Efetuar um pedido de associação, para um cliente particular, para criar uma associação "pendente" (IBAN e NIF diferentes da original)',
        25 => '1.19.2 Notificação de Pendente - Reativar associação depois da dissociação automática - Passo 2 - Verificar que foi recebida uma notificação a indicar que a associação original (1ª associação) se encontra no estado pendente',
        26 => '1.19.3 Notificação de Pendente - Reativar associação depois da dissociação automática - Passo 3 - Verificar que após a expiração da associação pendente é rececionada uma segunda notificação a indicar que a associação original (1ª associação) já não se encontra ativa',
        27 => '1.19.4 Notificação de Pendente - Reativar associação depois da dissociação automática - Passo 4 - Enviar novamente pedido de associação do primeiro passo',
    ];

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
        \Log::channel($this->logchannel)->info('__construct TestServiceV2');
    }

    public function __construct_3($logchannel, $tabPosition, $testNumber) 
    {
        $this->logchannel = $logchannel;
        $this->tabPosition =$tabPosition;
        $this->testNumber = $testNumber;

        \Log::channel($this->logchannel)->info('__construct_3 TestServiceV2');
    }

    public function getTestNamesPL()
    {
        return $this->testNamesPL;
    }

    public function getTestNamesIndisponibilidades()
    {
        return $this->testNamesIndisponibilidades;
    }

    public function getDescricaoTeste()
    {
        try {

            switch ( $this->tabPosition ){
                case 1: 
                     //PL
                     return $this->testNamesPL[ $this->testNumber];
    
                 case 2: 
                     //indisponibilidades
                     return $this->testNamesIndisponibilidades[ $this->testNumber];
    
                 default:
                     return 'Pedido de descrição inválido';
             }

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: Pedido de descição inválido -> ' . $e->getMessage();
        }
    
    }

    public function executeTest( $data )
    {
        try {

            switch ( $this->tabPosition ){
                case 1: 
                     //PL
                     return $this->executeTestPL( $data);
    
                 case 2: 
                     //indisponibilidades
                     return $this->executeTestIndisponibilidades( $data );
    
                 default:
                     return 'Pedido de execução de teste inválido';
             }

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: Pedido de execução de teste inválido -> ' . $e->getMessage();
        }
    
    }

    public function executeTestPL( $data )
    {
        try {

            //PL
            $interfacePL = new PL($this->logchannel, $this->tabPosition , $this->testNumber );

            if ( $interfacePL->setInterface() != '') {
                //houve erro
                return 'Pedido de setInterface inválido ';
            }
            \Log::channel($this->logchannel)->info('PL interface SET');

            if ( $interfacePL->setFields( $data  ) != '') {
                //houve erro
                return 'Pedido de setFields inválido ';
            }
            \Log::channel($this->logchannel)->info('PL fields SET');


            $response = $interfacePL->efetuaPedido();

            \Log::channel($this->logchannel)->info('PL efetuaPedido SET');
            \Log::channel($this->logchannel)->info( $response );

            return $response;

          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: Pedido de execução de teste inválido -> ' . $e->getMessage();
        }
    
    }
  
    public function executeTestIndisponibilidades( $data )
    {
        try {

            //indisponibilidades
            $interfaceIndisponibilidade = new Indisponibilidade($this->logchannel, $this->tabPosition , $this->testNumber );
           
            if ( $interfaceIndisponibilidade->setInterface() != '') {
                //houve erro
                return 'Pedido de setInterface inválido ';
            }
            \Log::channel($this->logchannel)->info('Indisponibilidade interface SET');

            if ( $interfaceIndisponibilidade->setFields( $data  ) != '') {
                //houve erro
                return 'Pedido de setFields inválido ';
            }
            \Log::channel($this->logchannel)->info('Indisponibilidade fields SET');

            $response = $interfaceIndisponibilidade->efetuaPedido();

            \Log::channel($this->logchannel)->info('Indisponibilidade efetuaPedido SET');
            \Log::channel($this->logchannel)->info( $response );

            return $response;
            
          }catch(\Exception $e){
            \Log::channel($this->logchannel )->error(__FILE__ . ' ' . __LINE__. ' Erro: ' . $e->getMessage());
            return 'Erro: Pedido de execução de teste inválido -> ' . $e->getMessage();
        }
    
    }

}