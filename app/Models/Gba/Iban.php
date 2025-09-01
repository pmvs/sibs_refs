<?php

namespace App\Models\Gba;

use DB;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Gba\SPais;
use App\Models\Gba\PMay;
use App\Models\Gba\SZib;

class Iban extends Model
{

    private $iban = '';
    private $nib = '';
    private $pais = '';
    private $paisnumber = '';
    private $banco = '';
    private $balcao = '';
    private $conta = '';
    private $tipo_conta = '';
    private $checkdigit = '';
    private $swift = '';
    private $nmbanco = '';
    private $nif = '';
    private $isValido = false;
    private $logchannel = 'transferencias';
    private $isConnProduction = '';
    protected $connection = 'odbc-gba';
    private $nomePrimeiroTitular = '';
    private $eInterno = false;
    private $temTeisSepa = 'N';
    private $checkIban = [];

        	/**
	 * Letter swapping according to IBAN's algorithm.
	 * @var $swaps
	 * @since  1.2.0
	 */
	protected $letters = ['A',  'B',  'C',  'D',  'E',  'F',  'G',  'H',  'I',  'J',  'K',  'L',  'M', 'N',  'O',  'P',  'Q',  'R',  'S',  'T',  'U',  'V',  'W',  'X',  'Y',  'Z'];

	protected $converted = ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35'];

	protected $banks = [
		'0001' => 'BANCO DE PORTUGAL, EP',
		'0005' => 'ABANCA SERVICIOS FINANCIEROS, E.F.C., S.A.- SUCURSAL EM PORTUGAL',
		'0007' => 'NOVO BANCO, SA',
		'0008' => 'BANCO BAI EUROPA, SA',
		'0010' => 'BANCO BPI, SA',
		'0014' => 'BANCO INVEST, SA',
		'0018' => 'BANCO SANTANDER TOTTA, SA',
		'0019' => 'BANCO BILBAO VIZCAYA  ARGENTARIA, S.A. - SUCURSAL EM PORTUGAL',
		'0022' => 'BANCO DO BRASIL AG - SUCURSAL EM PORTUGAL',
		'0023' => 'BANCO ACTIVOBANK, SA',
		'0025' => 'CAIXA - BANCO DE INVESTIMENTO, SA',
		'0032' => 'BARCLAYS BANK IRELAND PLC - SUCURSAL EM PORTUGAL',
		'0033' => 'BANCO COMERCIAL PORTUGUÊS, SA',
		'0034' => 'BNP PARIBAS',
		'0035' => 'CAIXA GERAL DE DEPÓSITOS, SA',
		'0036' => 'CAIXA ECONÓMICA MONTEPIO GERAL, CAIXA ECONÓMICA BANCÁRIA, SA',
		'0043' => 'DEUTSCHE BANK AKTIENGESELLSCHAFT - SUCURSAL EM PORTUGAL',
		'0045' => 'CAIXA DE CRÉDITO AGRÍCOLA, CRL',
		'1020' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE AROUCA, CRL',
		'1280' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO MÉDIO AVE, CRL',
		'1290' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE VILA VERDE E DE TERRAS DO BOURO, CRL',
		'1320' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE TERRAS DO SOUSA, AVE, BASTO E TÂMEGA, CRL',
		'1340' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO VALE DO SOUSA E BAIXO TÂMEGA, CRL',
		'1400' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE PAREDES, CRL',
		'1420' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO NOROESTE, CRL',
		'1440' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA ÁREA METROPOLITANA DO PORTO, CRL',
		'1460' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE PÓVOA DE VARZIM,VILA DO CONDE E ESPOSENDE, CRL',
		'1470' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO ALTO CÁVADO E BASTO, CRL',
		'2040' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO ALTO DOURO, CRL',
		'2090' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO BEIRA DOURO E LAFÕES, CRL',
		'2140' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO DOURO E CÔA, CRL',
		'2160' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO VALE DO TÁVORA E DOURO, CRL',
		'2190' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA TERRA QUENTE, CRL',
		'2230' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE TRÁS-OS-MONTES E ALTO DOURO, C.R.L.',
		'2260' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DOURO E SABOR, CRL',
		'3010' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO BAIXO MONDEGO, CRL',
		'3020' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE CANTANHEDE E MIRA, CRL',
		'3030' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE COIMBRA, CRL',
		'3060' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO VALE DO DÃO E ALTO VOUGA, CRL',
		'3090' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE OLIVEIRA DE AZEMÉIS E ESTARREJA, CRL',
		'3110' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE POMBAL, CRL',
		'3160' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE VALE DE CAMBRA, CRL',
		'3210' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE OLIVEIRA DO BAIRRO, CRL',
		'3220' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA COSTA VERDE, CRL',
		'3240' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO BAIXO VOUGA, CRL',
		'3310' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ALBERGARIA E SEVER, CRL',
		'3340' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE VAGOS, CRL',
		'3370' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DAS SERRAS DE ANSIÃO, CRL',
		'3380' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE OLIVEIRA DO HOSPITAL, CRL',
		'3400' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA BAIRRADA E AGUIEIRA, CRL',
		'3450' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO BEIRA CENTRO, CRL',
		'3470' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE TERRAS DE VIRIATO, CRL',
		'4020' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA REGIÃO DO FUNDÃO E SABUGAL, CRL',
		'4050' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA BEIRA BAIXA (SUL), CRL',
		'4080' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA SERRA DA ESTRELA, CRL',
		'4110' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA ZONA DO PINHAL, CRL',
		'5020' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ALCOBAÇA, CARTAXO, NAZARÉ, RIO MAIOR E SANTARÉM, CRL',
		'5050' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ALENQUER, CRL',
		'5060' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ARRUDA DOS VINHOS, CRL',
		'5070' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE AZAMBUJA, CRL',
		'5080' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA BATALHA, CRL',
		'5120' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE CADAVAL, CRL',
		'5130' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE CALDAS DA RAINHA, ÓBIDOS E PENICHE, CRL',
		'5140' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE LOURES, SINTRA E LITORAL, CRL',
		'5170' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE CORUCHE, CRL',
		'5190' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE LOURINHÃ, CRL',
		'5230' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE PERNES E ALCANHÕES, CRL',
		'5240' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE PORTO DE MÓS, CRL',
		'5270' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE SALVATERRA DE MAGOS, CRL',
		'5310' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE SOBRAL DE MONTE AGRAÇO, CRL',
		'5360' => 'CAIXA DE CREDITO AGRICOLA MUTUO DE VILA FRANCA DE XIRA, CRL',
		'5430' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO RIBATEJO NORTE E TRAMAGAL, CRL',
		'5460' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ENTRE TEJO E SADO, CRL',
		'5470' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO RIBATEJO SUL, CRL',
		'6020' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ALCÁCER DO SAL E MONTEMOR-O-NOVO, CRL',
		'6040' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ALJUSTREL E ALMODÔVAR, CRL',
		'6100' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO ALENTEJO SUL, CRL',
		'6110' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE BORBA, CRL',
		'6150' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO NORDESTE ALENTEJANO, CRL',
		'6160' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ELVAS E CAMPO MAIOR, CRL',
		'6170' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ESTREMOZ, MONFORTE E ARRONCHES, CRL',
		'6240' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE MORAVIS, CRL',
		'6250' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO GUADIANA INTERIOR, CRL',
		'6320' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA COSTA AZUL, CRL',
		'6330' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE SÃO TEOTÓNIO, CRL',
		'6430' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO NORTE ALENTEJANO, CRL',
		'6440' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO ALENTEJO CENTRAL, CRL',
		'7010' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE ALBUFEIRA, CRL',
		'7120' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE SÃO BARTOLOMEU DE MESSINES E SÃO MARCOS DA SERRA, CRL',
		'7130' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE SILVES, CRL',
		'7140' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO SOTAVENTO ALGARVIO, CRL',
		'7210' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DO ALGARVE, CRL',
		'8050' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DOS AÇORES, CRL',
		'9000' => 'CAIXA CENTRAL - CAIXA CENTRAL DE CRÉDITO AGRÍCOLA MÚTUO, CRL',
		'0047' => 'HAITONG BANK, SA',
		'0048' => 'BANCO FINANTIA, SA',
		'0057' => 'CAIXA ECONÓMICA DO PORTO',
		'0059' => 'CAIXA ECONÓMICA DA MISERICÓRDIA DE ANGRA DO HEROÍSMO, CAIXA ECONÓMICA BANCÁRIA, SA',
		'0060' => 'BANCO MADESANT - SOCIEDADE UNIPESSOAL, SA',
		'0061' => 'BANCO DE INVESTIMENTO GLOBAL, SA',
		'0063' => 'BISON BANK, S.A.',
		'0064' => 'BANCO PORTUGUÊS DE GESTÃO, SA',
		'0065' => 'BEST - BANCO ELECTRÓNICO DE SERVIÇO TOTAL, SA',
		'0073' => 'BANCO SANTANDER CONSUMER PORTUGAL, SA',
		'0076' => 'MONTEPIO INVESTIMENTO, SA',
		'0079' => 'BANCO BIC PORTUGUÊS, SA',
		'0082' => 'FCE BANK PLC',
		'0086' => 'BANCO EFISA, SA',
		'0097' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DA CHAMUSCA, CRL',
		'0098' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE BOMBARRAL, CRL',
		'0160' => 'NOVO BANCO DOS AÇORES, SA',
		'0169' => 'CITIBANK EUROPE PLC - SUCURSAL EM PORTUGAL',
		'0170' => 'ABANCA CORPORACION BANCARIA, SA - SUCURSAL EM PORTUGAL',
		'0171' => 'RCI BANQUE - SUCURSAL PORTUGAL',
		'0172' => 'BMW BANK GMBH - SUCURSAL PORTUGUESA',
		'0173' => 'EDMOND DE ROTHSCHILD EUROPE - SUCURSAL EM PORTUGAL',
		'0189' => 'BANCO ATLÂNTICO EUROPA, SA',
		'0191' => 'BNI - BANCO DE NEGÓCIOS INTERNACIONAL (EUROPA), SA',
		'0193' => 'BANCO CTT, SA',
		'0195' => 'ITAÚ BBA EUROPE, S.A.',
		'0235' => 'BANCO L.J. CARREGOSA, SA',
		'0238' => 'BNP PARIBAS LEASE GROUP, SA',
		'0246' => 'BANCO PRIMUS, SA',
		'0257' => 'BNP PARIBAS SECURITIES SERVICES - SUCURSAL EM PORTUGAL',
		'0259' => 'DE LAGE LANDEN INTERNATIONAL, B.V. - SUCURSAL EM PORTUGAL',
		'0264' => 'VOLKSWAGEN BANK GMBH - SUCURSAL EM PORTUGAL',
		'0266' => 'BANK OF CHINA (LUXEMBOURG), SA LISBON BRANCH - SUCURSAL EM PORTUGAL',
		'0267' => 'CREDIT SUISSE (LUXEMBOURG), SA - SUCURSAL EM PORTUGAL',
		'0269' => 'BANKINTER, SA - SUCURSAL EM PORTUGAL',
		'0270' => 'IBM DEUTSCHLAND KREDITBANK GMBH - SUCURSAL EM PORTUGAL',
		'0271' => 'TOYOTA KREDITBANK GMBH – SUCURSAL EM PORTUGAL',
		'0272' => 'WIZINK BANK, SA - SUCURSAL EM PORTUGAL',
		'0274' => 'CECABANK, SA - SUCURSAL EM PORTUGAL',
		'0275' => 'BANCO SABADELL, SA - SUCURSAL EM PORTUGAL',
		'0276' => 'BANCA FARMAFACTORING SPA - SUCURSAL EM PORTUGAL',
		'0277' => 'CAIXABANK,S.A.  - SUCURSAL EM PORTUGAL',
		'0278' => 'GRENKE BANK AG - SUCURSAL EM PORTUGAL',
		'0280' => 'EFG BANK (LUXEMBOURG) S.A. - SUCURSAL EM PORTUGAL',
		'0305' => '321 CRÉDITO - INSTITUIÇÃO FINANCEIRA DE CRÉDITO, SA',
		'0314' => 'SOFID - SOCIEDADE PARA O FINANCIAMENTO DO DESENVOLVIMENTO, INSTITUIÇÃO FINANCEIRA DE CRÉDITO, SA',
		'0329' => 'REALTRANSFER - INSTITUIÇÃO DE PAGAMENTOS, SA',
		'0500' => 'ING BANK NV - SUCURSAL EM PORTUGAL',
		'0698' => 'UNICRE - INSTITUIÇÃO FINANCEIRA DE CRÉDITO, SA',
		'0771' => 'CREDIT AGRICOLE LEASING & FACTORING - SUCURSAL EM PORTUGAL',
		'0780' => 'FCA CAPITAL PORTUGAL, INSTITUIÇÃO FINANCEIRA DE CRÉDITO, SA',
		'0781' => 'AGENCIA DE GESTAO DA TESOURARIA E DA DIVIDA PUBLICA - IGCP, E.P.E.',
		'0796' => 'MONTEPIO CRÉDITO - INSTITUIÇÃO FINANCEIRA DE CRÉDITO, SA',
		'0800' => 'BBVA, INSTITUIÇÃO FINANCEIRA DE CRÉDITO, SA',
		'0812' => 'NOVACÂMBIOS - INSTITUIÇÃO DE PAGAMENTO, SA',
		'0824' => 'UNICÂMBIO - INSTITUIÇÃO DE PAGAMENTO, SA',
		'0848' => 'BNP PARIBAS PERSONAL FINANCE, S.A. - SUCURSAL EM PORTUGAL',
		'0881' => 'ONEY BANK - SUCURSAL EM PORTUGAL',
		'0916' => 'BANCO CREDIBOM, SA',
		'0921' => 'COFIDIS',
		'0955' => 'OREY FINANCIAL - INSTITUICAO FINANCEIRA DE CREDITO, SA',
		'5180' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE LEIRIA, CRL',
		'5200' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE MAFRA, CRL',
		'5340' => 'CAIXA DE CRÉDITO AGRÍCOLA MÚTUO DE TORRES VEDRAS, CRL',
		'7500' => 'SFS – FINANCIAL SERVICES, IME, S.A.',
		'7837' => 'VIVA PAYMENT SERVICES SA',
		'8115' => 'CTT - CORREIOS DE PORTUGAL, SA',
		'8700' => 'LUSOPAY, INSTITUIÇÃO DE PAGAMENTO, LDA',
		'8701' => 'PAYSHOP (PORTUGAL), SA',
		'8703' => 'SIBS PAGAMENTOS, SA',
		'8705' => 'ALTICE PAY, SA',
		'8706' => 'EASYPAY - INSTITUIÇÃO DE PAGAMENTO, LDA',
		'8707' => 'IFTHENPAY, LDA',
		'8708' => 'MAXPAY - INSTITUIÇÃO DE PAGAMENTO, LDA',
		'8709' => 'EUPAGO - INSTITUIÇÃO DE PAGAMENTO, LDA',
		'8710' => 'PAYPAYUE - INSTITUIÇÃO DE PAGAMENTO, UNIPESSOAL, LDA',
		'8711' => 'RAIZE - INSTITUIÇÃO DE PAGAMENTOS, SA',
		'8863' => 'MONTY GLOBAL PAYMENTS, S.A.',
		'8987' => 'LUFTHANSA AIRPLUS SERVICEKARTEN GMBH - SUCURSAL EM PORTUGAL',
	];

   
    public function __construct() 
    {
        $this->logchannel = 'cops';

        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();

        if (method_exists($this, $method_name = '__construct'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct Iban');

    }

    public function __construct1($iban)
    {
        
        $this->logchannel = 'cops';
        \Log::channel($this->logchannel)->info('__construct1 Iban');
        $this->isValido = false;
        $this->iban = $iban;
        $this->desmaterializa();
    }

 
    public function __construct2( $iban, $logchannel)
    {
       \Log::channel($logchannel)->info('__construct2 Iban');
        $this->logchannel = $logchannel;
        $this->isValido = false;
        $this->iban = $iban;
        $this->desmaterializa();
    }


    private function desmaterializa() {
        try {

            $this->pais =  substr( $this->iban, 0, 2 );
            $this->paisnumber =  substr( $this->iban, 2 , 2 );

            $tamanhoiban = 25; //PT
            $infopais = SPais::getInfoPais($this->pais, $this->logchannel);
            if ( $infopais ) {
                if ( count( $infopais ) == 1 ) {
                    $tamanhoiban =  $infopais[0]->n_dig_iban;
                }
            }else {
                \Log::channel($this->logchannel)->warning('Tamanho Iban: 25 FIXO. Não foi possivel obter informacao dos paises');
            }
            \Log::channel($this->logchannel)->info('Tamanho Iban: '. $tamanhoiban);

            $this->nib =  substr( $this->iban, 4, $tamanhoiban );
            $this->banco =  substr( $this->iban, 4 , 4 );
            $this->balcao =  substr( $this->iban, 8 , 4 );
            $this->conta =  substr( $this->iban, 12, -5);
            //\Log::channel($this->logchannel)->info('Conta: '.  $this->conta );

            $this->tipo_conta =  substr( $this->iban, $tamanhoiban - 5, 3 );
            $this->checkdigit =  substr( $this->iban, -2 );
            $this->eInterno  = ($this->banco == $this->getCodigoBanco() ?  true : false);
            // $this->trataInfoBanco();
            //$this->isIbanValido($this->pais , $this->paisnumber, $this->nib);
            $this->nomePrimeiroTitular = '';
        
            $this->checkIban = $this->check([$this->iban]);
           // $this->setNomePrimeiroTitularNIF() ;
        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
       

    }

    private function trataInfoBanco(){

        $this->swift  = '';
        $this->nmbanco  = '';

        if ( $this->pais ) {

            $infopais = SPais::getInfoPais($this->pais);
            if ( $infopais) {

                $primdig = $infopais[0]->prim_dig_banco;
                $ultdig = $infopais[0]->ult_dig_banco;

                if ( $this->pais == 'PT' ) {
                    $ibanpart = substr($this->iban, 4, 4 );
                } else {
                    $ibanpart = substr($this->iban, 4, ( $ultdig - $primdig + 1) );
                }

                $infobanco = PMay::getInfo( $this->pais, $ibanpart );
                if ( $infobanco ) {
                    if ( count($infobanco) == 1 ) {
                        $this->nmbanco  = $infobanco[0]->nm_banco;
                        $this->swift  = $infobanco[0]->bic;
                    }else {
                        $this->nmbanco  = 'N.A.';
                        $this->swift= 'N.A.';
                    }
                }else {
                    $this->nmbanco = 'N.A.';
                    $this->swift = 'N.A.';
                }

                $infoTeis = SZib::getInfo($this->banco);
                if ( $infoTeis ) {
                    if ( count($infoTeis) == 1 ) {
                        $this->temTeisSepa  = $infoTeis[0]->tem_teis_sepa;
                    }else {
                        $this->temTeisSepa  = 'N';
                    }
                }else {
                    $this->temTeisSepa = 'N';
                }

            }
        }

    }
    
    private function isIbanValido( $paisdest, $codpaisdest, $ibandest ) {

        try {

            $this->isValido = false;

            $ibancompleto = trim ( $paisdest ) . trim ( $codpaisdest ) . trim ( $ibandest );

            //\Log::channel( $this->logchannel )->info('Vai validar IBAN...:' . $ibancompleto);

            $infopais = SPais::getInfoPais($paisdest);

            if ( $infopais ) {

                //\Log::channel( $this->logchannel )->info('Pais...:' . print_r( $infopais, true));

                if ( count( $infopais ) == 1 ) {

                    $ndigiban =  $infopais[0]->n_dig_iban;
                    $tamanhoiban = strlen(  trim($ibancompleto) );

                    // \Log::channel( $this->logchannel )->info('Tamanho IBAN...:' . $tamanhoiban);
                    // \Log::channel( $this->logchannel )->info('Digitos IBAN...:' . $ndigiban);

                    if ( $tamanhoiban == $ndigiban ) {
                        //\Log::channel( $this->logchannel )->info('IBAN...: Válido' );
                        $this->isValido = true;
                        return;
                    }

                }

            }

            //\Log::channel( $this->logchannel )->info('IBAN...: Inválido' );

            $this->isValido = false;

        } catch ( Exception $e) {
            \Log::channel( $this->logchannel )->error( $e->getMessage() );
           // Log::error('IBAN...: Inválido' );
            $this->isValido = false;
        }
    

    }

    public function print()
    {
        try {
     
            \Log::channel($this->logchannel)->info( '-------------------------');
            \Log::channel($this->logchannel)->info( '----------IBAN-----------');
            \Log::channel($this->logchannel)->info( 'País  : ' . $this->pais );
            \Log::channel($this->logchannel)->info( 'Code  : ' . $this->paisnumber );
            \Log::channel($this->logchannel)->info( 'NIB   : ' . $this->nib );
            \Log::channel($this->logchannel)->info( 'Banco : ' . $this->banco );
            \Log::channel($this->logchannel)->info( 'Balcao: ' . $this->balcao );
            \Log::channel($this->logchannel)->info( 'Conta : ' . $this->conta );
            \Log::channel($this->logchannel)->info( 'Tipo Conta : ' . $this->tipo_conta );
            \Log::channel($this->logchannel)->info( 'ChkDgt: ' . $this->checkdigit );
            \Log::channel($this->logchannel)->info( 'Swift : ' . $this->swift );
            \Log::channel($this->logchannel)->info( 'Nm.Ban: ' . $this->nmbanco );
            // \Log::channel($this->logchannel)->info( 'Size Válido: ' . ($this->isValido ? 'SIM':'NÃO') );
            \Log::channel($this->logchannel)->info( 'Nome 1º titular: ' . $this->nomePrimeiroTitular );
            \Log::channel($this->logchannel)->info( 'Nif   : ' . $this->nif );
            \Log::channel($this->logchannel)->info( 'Teis SEPA   : ' . $this->temTeisSepa );
            \Log::channel($this->logchannel)->info( 'Check   : ' . print_r($this->checkIban, true) );
            \Log::channel($this->logchannel)->info( 'Check   : ' . ( $this->checkIban[''. $this->iban. ''] ? "S": "N") );
            \Log::channel($this->logchannel)->info( '-------------------------');
      
        } catch ( \Exception $e) {
            \Log::channel( $this->logchannel )->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
    }

    public function getInfoIBAN()
    {
        try {
     
            $aux = '----------IBAN-----------' . PHP_EOL ;
            $aux .= 'País  : ' . $this->pais . PHP_EOL ;
            $aux .= 'Code  : ' . $this->paisnumber . PHP_EOL ;
            $aux .= 'NIB   : ' . $this->nib . PHP_EOL ;
            $aux .= 'Banco : ' . $this->banco . PHP_EOL ;
            $aux .= 'Balcao: ' . $this->balcao . PHP_EOL ;
            $aux .= 'Conta : ' . $this->conta . PHP_EOL ;
            $aux .= 'Tipo Conta : ' . $this->tipo_conta . PHP_EOL ;
            $aux .= 'ChkDgt: ' . $this->checkdigit . PHP_EOL ;
            $aux .= 'Swift : ' . $this->swift . PHP_EOL ;
            $aux .= 'Nm.Ban: ' . $this->nmbanco . PHP_EOL ;
            //$aux .= 'Size Válido: ' . ($this->isValido ? 'SIM':'NÃO') . PHP_EOL ;
            $aux .= 'Nome 1º titular: ' . $this->nomePrimeiroTitular . PHP_EOL ;
            $aux .= 'Nif   : ' . $this->nif . PHP_EOL ;
            $aux .= 'Teis SEPA   : ' . $this->temTeisSepa . PHP_EOL ;
            $aux .= 'Check   : ' . print_r($this->checkIban, true) . PHP_EOL ;
            $aux .= 'Check   : ' . ( $this->checkIban[''. $this->iban. ''] ? "S": "N") . PHP_EOL ;
            $aux .= 'Check IBAN  : ' . $this->checkIBAN( $this->iban) . PHP_EOL ;
            $aux .=  '-------------------------'.  PHP_EOL ;
      
            return $aux;

        } catch ( \Exception $e) {
            \Log::channel( $this->logchannel )->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
        }
    }

     /*
    |--------------------------------------------------------------------------
    | SETTERS
    |--------------------------------------------------------------------------
    */  
    public function setLogChannel( $logchannel )
    {
        $this->logchannel = $logchannel;
    }

    public function setIsConnProduction( $isConnProduction )
    {
        $this->isConnProduction = $isConnProduction;
        if ( $this->isConnProduction ) {
            $this->connection = 'odbc-gba-prod';
        }else {
            $this->connection = 'odbc-gba';
        }
        \Log::channel($this->logchannel)->info('setIsConnProduction : ' . $this->connection);
    }

    public function getCheck()
    {
       return $this->checkIban[''. $this->iban. ''] ? true: false;
    }

    public function getIban() {
        return $this->iban;
    }
    public function getNIB() {
        return $this->nib;
    }
    public function getPaisIban() {
        return $this->pais;
    }
    public function getPaisNumberIban() {
        return $this->paisnumber;
    }
    public function getBancoIban() {
        return $this->banco;
    }
    public function getBalcaoIban() {
        return $this->balcao;
    }
    public function getContaIban() {
        return $this->conta;
    }
    public function getCheckDigitIban() {
        return $this->checkdigit;
    }
    public function getSwift() {
        return $this->swift;
    }
    public function getNomeBanco() {
        return $this->nmbanco;
    }
    public function getIsValido() {
        return $this->isValido;
    }
    public function getNomePrimeiroTitular() {
        return $this->nomePrimeiroTitular;
    }
    public function getNif() {
        return $this->nif;
    }
    public function eInterno() {
        return $this->eInterno;
    }
    public function getTipoConta() {
        return $this->tipo_conta;
    }
    public function getTemTeisSepa() {
        return $this->temTeisSepa;
    }

    public static function constroiIban($key) {

        $iban = "PT50" . $key['cd_banco'] . $key['cd_balcao']  . str_pad($key['cd_entidade'],8,"0",STR_PAD_LEFT) . str_pad($key['tp_cnt'],3,"0",STR_PAD_LEFT). str_pad($key['cdv_nib'],2,"0",STR_PAD_LEFT);
        return $iban;

    }

    public static function valido( $paisdest, $codpaisdest, $ibandest ) {

        try {

            $ibancompleto = trim ( $paisdest ) . trim ( $codpaisdest ) . trim ( $ibandest );

            //Log::info('Vai validar IBAN...:' . $ibancompleto);

            $infopais = SPais::getInfoPais($paisdest);

            if ( $infopais ) {

                if ( count( $infopais ) == 1 ) {

                    $ndigiban =  $infopais[0]->n_dig_iban;
                    $tamanhoiban = strlen(  trim($ibancompleto) );

                    // Log::info('Tamanho IBAN...:' . $tamanhoiban);
                    // Log::info('Digitos IBAN...:' . $ndigiban);

                    if ( $tamanhoiban == $ndigiban ) {
                      //  Log::info('IBAN...: Válido' );
                        return true;
                    }

                }

            }

           // Log::info('IBAN...: Inválido' );

            return false;

        } catch ( Exception $e) {
            Log::error( $e->getMessage() );
            //Log::error('IBAN...: Inválido' );
            return false;
        }
    

    }

    public static function validate($iban) {
       
        $iban = strtolower(str_replace(' ','',$iban));
        $Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
        $Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

        if(strlen($iban) == $Countries[substr($iban,0,2)]){

            $MovedChar = substr($iban, 4).substr($iban,0,4);
            $MovedCharArray = str_split($MovedChar);
            $NewString = "";

            foreach($MovedCharArray AS $key => $value){
                if(!is_numeric($MovedCharArray[$key])){
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if(bcmod($NewString, '97') == 1)
            {
                return true;
            }
        }
        return false;
    }

    public function setNomePrimeiroTitularNIF()
    {
      
        if ($this->conta == '') {
            \Log::channel( $this->logchannel )->warning('Conta vazia para o IBAN : '. $this->iban);
            return true;
        }
        if ($this->checkdigit == '') {
            \Log::channel( $this->logchannel )->warning('Checkdigit vazio para o IBAN : '. $this->iban);
            return true;
        }
        if ($this->balcao == '') {
            \Log::channel( $this->logchannel )->warning('Balcao vazia para o IBAN : '. $this->iban);
            return true;
        }
        //\Log::channel( $this->logchannel )->info('Conta : '. $this->conta);
        $cdentidade = str_replace('.','',$this->conta);
        $cdentidade =  substr((int) $cdentidade,0,-3); 
        // \Log::channel( $this->logchannel )->info('Conta : '. $this->conta);
        if ( trim($cdentidade) == '') {
            \Log::channel( $this->logchannel )->warning('Conta entidade vazia para o IBAN : '. $this->iban);
            return true;
        }

        $sql ="select  'PT50'||
                lpad(bparametros.cd_banco,4,'0')||
                lpad(bcnt.cd_balcao,4,'0')||
                lpad(bcnt.cd_entidade,8,'0')||
                lpad(bcnt.tp_cnt,3,'0')||
                lpad(bcnt.cdv_nib,2,'0')
                IBAN,
                lpad(bparametros.cd_banco,4,'0') cd_banco,
                lpad(bcnt.cd_balcao,4,'0') cd_balcao,
                lpad(bcnt.cd_entidade,8,'0') cd_entidade,
                lpad(bcnt.tp_cnt,3,'0') tp_cnt,
                lpad(bcnt.cdv_nib,2,'0') cdv_nib,
                bentidade_id.tp_doc_id,
                bentidade_id.n_doc_id,
                bentidade_id.n_titular,
                bidentif.n_f_contr,
                bidentif.nome
            from bcnt,
                    btp_cnt,
                    bparametros,
                    bentidade_id,
                    bidentif
            where bcnt.tp_entidade=1
                and bcnt.cd_entidade=$cdentidade
                and bcnt.tp_cnt=1
                and bcnt.cd_balcao=$this->balcao
                and bcnt.cdv_nib='$this->checkdigit'
                and btp_cnt.tipo_conta='DO'
                and btp_cnt.tp_cnt=bcnt.tp_cnt
                and bentidade_id.n_titular=1
                and bentidade_id.tp_entidade=bcnt.tp_entidade
                and bentidade_id.cd_entidade=bcnt.cd_entidade
                and bidentif.tp_doc_id=bentidade_id.tp_doc_id
                and bidentif.n_doc_id=bentidade_id.n_doc_id";

     
        //\Log::channel($this->logchannel)->info('IBAN: setNomePrimeiroTitular : ' . $sql);

        try {   
            $this->connection = 'odbc-gba-prod';
            $devolve = DB::connection( $this->connection )->select($sql); 
            //\Log::channel( $this->logchannel )->info(print_r( $devolve, true));
            if ( $devolve ) {
                if ( count($devolve) > 0 ) {
                    $this->nomePrimeiroTitular = trim($devolve[0]->nome);
                    $this->nif = trim($devolve[0]->n_f_contr);
                }
            }


        } catch ( \QueryException $e) {
            \Log::channel( $this->logchannel )->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $devolve = '';
        } catch ( \Exception $e) {
            \Log::channel( $this->logchannel )->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $devolve = '';
        }
       
       // \Log::channel($this->logchannel)->info('Vai devolver  : ' . print_r( $devolve, true));
      
       
        return true;

    }

    public static function getIBANSFromPetc( $logchannel, $isConnProduction)
    {
   
        $sql ="select distinct iban_ordenante from petc where dt_a_lancar > '2023-10-16'";
        $sql ="select distinct iban_ordenante, iban_dst from petc where dt_a_lancar > '2023-10-19'";
    
        \Log::channel($logchannel)->info($sql);

        try {   
            $connection = 'odbc-gba-prod';
            if ( $isConnProduction ) {
                $connection = 'odbc-gba-prod';
            }

            $devolve = DB::connection( $connection )->select($sql); 
        } catch ( \QueryException $e) {
            \Log::channel( $logchannel )->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $devolve = [];
        } catch ( \Exception $e) {
            \Log::channel( $logchannel )->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage() );
            $devolve = [];
        }
    
        \Log::channel($logchannel)->info('Vai devolver  : ' . print_r( $devolve, true));
      
        return $devolve;

    }

    private function getCodigoBanco() 
    {

        // $parametros = Bparametros::getAll();

        // if ( $parametros ) {
        //     if ( count($parametros) == 1 ) {
        //         return $parametros[0]['cd_balcao'];
        //     }
        // }
        
        $cdbanco = '0000';

        //caso nao consiga ir ao bparametros
        switch( env('APP_SIGLA') ) {
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
                $cdbanco = '0000';
                break;
        }

        return $cdbanco;

    }


	public function check(array $codes)
	{
		$results = [];

		foreach($codes as $code) {
			$results[$code] = $this->validate2($code);
		}

		return $results;
	}

	public function validate2(string $string)
	{
		$string = strtoupper(str_replace(' ', '', $string));
        $string = substr($string, 4) . substr($string, 0, 4);

        $string = str_replace(
            $this->letters,
            $this->converted,
            $string
        );

        $process = 0;

        for ($i = 0; $i < strlen($string); $i++) {

            $process = $process * 10;
            $process = $process + intval(substr($string, $i, 1));
            $process = $process % 97;
        }

        if($process == 1) {
        	return true;
        } else {
        	return false;
        }

	}

	public function getBank(string $string)
	{
		$string = substr($string, 4, 4);

		return $this->banks[$string];
	}

    public function checkIBAN($iban)
    {
        if(strlen($iban) < 5) return false;
        $iban = strtolower(str_replace(' ','',$iban));
        $Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
        $Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

        if(array_key_exists(substr($iban,0,2), $Countries) && strlen($iban) == $Countries[substr($iban,0,2)]){
                    
            $MovedChar = substr($iban, 4).substr($iban,0,4);
            $MovedCharArray = str_split($MovedChar);
            $NewString = "";

            foreach($MovedCharArray AS $key => $value){
                if(!is_numeric($MovedCharArray[$key])){
                    if(!isset($Chars[$MovedCharArray[$key]])) return false;
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }
            
            if(bcmod($NewString, '97') == 1)
            {
                return true;
            }
        }
        return false;
    }

}