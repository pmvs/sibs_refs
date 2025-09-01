<?php

return [

    'whiteIps' => [
        '127.0.0.1', //localhost
        '193.176.0.144', //BdP Mafr
        '193.176.0.150', //BdP BOM PRD
        '193.176.0.160', //BdP BOM SPP
        '192.168.99.254', //BdP BOM SPP
        '213.58.181.135', //Bdp Tvd
        '172.26.9.182', //BdP CHM
        '200.52.0.127', //homebanking
        '144.64.40.56', //pedro santos casa  
        '2.82.183.158',//pedro santos casa 
        '2.82.204.91',//pedro santos casa 
        //'192.168.1.216', //Bdp Bom
    ],

    'apibp' => [

        'api_plcp' => [
            'COPB' => 1,
            'PROXYLOOKUP_GESTAO' => 2,
            'PROXYLOOKUP_CONSULTAS' => 3,
        ],

        'resources_plcp' => [
            'OAUTH2' => 'https://www.bportugal.net/adfs/oauth2/token',
            'COPB' => 'https://www.bportugal.net/apigw/conp/',
            'PROXYLOOKUP_GESTAO' => 'https://www.bportugal.net/apigw/pl/',
            'PROXYLOOKUP_CONSULTAS' => 'https://www.bportugal.net/apigw/pl/',
        ],
    
        'endpoints_plcp' => [

            'INDISPONIBILIDADE_NAO_PROGRAMADA_CREATE' => 'https://www.bportugal.net/apigw/conp/unavailability/unexpected/create',
            'INDISPONIBILIDADE_NAO_PROGRAMADA_UPDATE' => 'https://www.bportugal.net/apigw/conp/unavailability/unexpected/update',
            'INDISPONIBILIDADE_PROGRAMADA_CREATE' => 'https://www.bportugal.net/apigw/conp/unavailability/planned/create',
            'INDISPONIBILIDADE_PROGRAMADA_UPDATE' => 'https://www.bportugal.net/apigw/conp/unavailability/planned/update',
            'INDISPONIBILIDADE_LIST' => 'https://www.bportugal.net/apigw/conp/unavailability/list',


            'COPS' => 'https://www.bportugal.net/apigw/conp/cops',
            'COPB' => 'https://www.bportugal.net/apigw/conp/copb',
            'COP_HEALTH' => 'https://www.bportugal.net/apigw/conp/health',
            'PL_GESTAO_INSERT_ASSOC' => 'https://www.bportugal.net/apigw/pl/mgmt/insert',
            'PL_GESTAO_DELETE_ASSOC' => 'https://www.bportugal.net/apigw/pl/mgmt/delete',
            'PL_GESTAO_HEALTH' => 'https://www.bportugal.net/apigw/pl/mgmt/health',
            'PL_CONSULTA_CONTACTS' => 'https://www.bportugal.net/apigw/pl/lookup/contacts',
            'PL_CONSULTA_ACCOUNT' => 'https://www.bportugal.net/apigw/pl/lookup/account',
            'PL_CONSULTA_CONFIRMATION' => 'https://www.bportugal.net/apigw/pl/lookup/confirmation',
            'PL_CONSULTA_HEALTH' => 'https://www.bportugal.net/apigw/pl/lookup/health',
        ],
    ],

    'apibp_dev' => [

        'api_plcp' => [
            'COPB' => 1,
            'PROXYLOOKUP_GESTAO' => 2,
            'PROXYLOOKUP_CONSULTAS' => 3,
        ],

        'resources_plcp' => [
            'OAUTH2' => 'https://wwwcert.bportugal.net/adfs/oauth2/token',
            'COPB' => 'https://wwwcert.bportugal.net/apigw/conp/',
            'PROXYLOOKUP_GESTAO' => 'https://wwwcert.bportugal.net/apigw/pl/',
            'PROXYLOOKUP_CONSULTAS' => 'https://wwwcert.bportugal.net/apigw/pl/',
        ],
    
        'endpoints_plcp' => [
            
            'INDISPONIBILIDADE_NAO_PROGRAMADA_CREATE' => 'https://wwwcert.bportugal.net/apigw/conp/unavailability/unexpected/create',
            'INDISPONIBILIDADE_NAO_PROGRAMADA_UPDATE' => 'https://wwwcert.bportugal.net/apigw/conp/unavailability/unexpected/update',
            'INDISPONIBILIDADE_PROGRAMADA_CREATE' => 'https://wwwcert.bportugal.net/apigw/conp/unavailability/planned/create',
            'INDISPONIBILIDADE_PROGRAMADA_UPDATE' => 'https://wwwcert.bportugal.net/apigw/conp/unavailability/planned/update',
            'INDISPONIBILIDADE_LIST' => 'https://wwwcert.bportugal.net/apigw/conp/unavailability/list',

            'COPS' => 'https://wwwcert.bportugal.net/apigw/conp/cops',
            'COPB' => 'https://wwwcert.bportugal.net/apigw/conp/copb',
            'COP_HEALTH' => 'https://wwwcert.bportugal.net/apigw/conp/health',
            'PL_GESTAO_INSERT_ASSOC' => 'https://wwwcert.bportugal.net/apigw/pl/mgmt/insert',
            'PL_GESTAO_DELETE_ASSOC' => 'https://wwwcert.bportugal.net/apigw/pl/mgmt/delete',
            'PL_GESTAO_HEALTH' => 'https://wwwcert.bportugal.net/apigw/pl/mgmt/health',
            'PL_CONSULTA_CONTACTS' => 'https://wwwcert.bportugal.net/apigw/pl/lookup/contacts',
            'PL_CONSULTA_ACCOUNT' => 'https://wwwcert.bportugal.net/apigw/pl/lookup/account',
            'PL_CONSULTA_CONFIRMATION' => 'https://wwwcert.bportugal.net/apigw/pl/lookup/confirmation',
            'PL_CONSULTA_HEALTH' => 'https://wwwcert.bportugal.net/apigw/pl/lookup/health',
        ],
    ],

    'testes' => [
        'sms', 
        'email', 
        'msg', 
        'sibs', 
        'api',
        'hash',
        'listas',
        'exceptions',
        'attacks',
        'ia-text',
        'ia-models',
        'movimentos',
        'cartoes',
        'pan',
        'oba_v1',
        'oba_v2',
        'oba_v3',
        'oba_v4',
        'bp',
        'proxylookup',
    ],

    'arrayTiposTABancoPortugal' => [
        'DNS1',
        'DNAP',
        'DNS1_i',
        'DNAP_i'
    ],

    'arrayInstrumentosBancoPortugal' => [
        'TA',
        'CH',
        'TR',
        'DD',
    ],

    'arrayTemasBancoPortugal' => [
        'OP',
        'RT',
        'FR',
        'PR',
        'DL',
        'DNS1',
        'DNAP',
        'DNS1_i',
        'DNAP_i',
    ],

    'attacks' => [
        'acessos', 
    ],
    
    'listas' => [
        'all', 
    ],

    'exceptions' => [
        'query',
        '400', 
        '403', 
        '404', 
        '500', 
    ],

    'tiposmensagensteste' => [
        'LOGIN',
        'PASSENCCON' ,
        'PASSENCMOV',
        'PSSU' ,
        'PAG_ESTADO',
        'PAG_SERV',
        'PAG_TSU',
        'TRF',
        'CARREGAMENTO',
        'CONSENT_OTP',
        'PAYMENT_OTP' ,
        'BULKPAYMENT_OTP',
        'BULKPAYMENT_TSU' ,
        'NOTIFICA_TRANSFERENCIA',
        'ENVIO_PINS',
        'OTPC2B',
        'REPPIN',
        'NOTIFY_MULTIAUTH',
        'EXCEPTION',
        'LOGIN_MULTIAUTH',
        'NOTIFY_RESET_PINS',
        'TESTE',
        'LIST_ALL_ASPSP',
    ],

    'tiposmensagensoba' => [
        'LIST_ALL_ASPSP',
        'LIST_ALL_TPP', 
    ],

    'mensagensH2H' => [
        'H002',
        'H004',
        'H008',
        'H201', 
        'H202', 
        'H310',
        'H313',
        'H473',
        'H524',
    ],

    'tamanhoMensagensH2H' => [
        'H002' => 41,
        'S002OK' => 199,
        'S002NOK' => 135,
        'H004' => 73,
        'S004OK' => 176,
        'S004NOK' => 135,
        'H524' => 302,
        'S524OK' => 141,
        'S524NOK' => 135,
    ],

    'mensagensAPI' => [
        'parametros',
        'bic',
        'paises',
        'atividades',
        'profissoes',
        'movimentos',
    ],
  
  
    'allowedActions' => [
        'acesso',
        'criar',
        'alterar',
        'consultar',
        'eliminar',
        'atualizar',
        'leitura',
    ],

    'allowedDatabases' => [
        'listas_sanc', 
        'netcaixa', 
        'balcoes', 
        'ifx', 
        'ifx:gba',
        'mysql:ccidadao',
        'mysql:listas_sanc',
        'api:watchlist',
    ],

    'allowedModules' => [
        'Branqueamento Capitais', 
        'Cartao do Cidadao', 
        'Manutencoes Netcaixa',
        'API DowJones',
    ],

    'allowedSubModules' => [
        'Menus', 
        'Contas Abertas', 
        'Desbloqueio de Contas', 
        'Avaliacao de Listas', 
        'Avaliacao de Operacoes',  
        'Manutencoes Utilizadores', 
        'Alteracao Perfil', 
        'Atualização Listas Risco', 
        'Leitura de Cartao do Cidadao',
        'Pesquisa de Nome',
    ],
 
    'tipoOperacaoProxyLookup' => [
        'Insert'  => '1',  
        'Delete' => '2',
        'Confirmation' => '3',
        'Health', 
        'Account' => '5', 
        'Contacts' => '4',  
        'Removed_Association',  
        'Expired_Association',

    ],

    'tipoOperacaoCop' => [
        'Cops', 
        'Copb', 
        'Health', 
    ],

    'ProxyLookupTipoIdentificador' => [
        'Telemovel' => '1', 
        'NIPC' => '2' ,
    ],

    'ProxyLookupTipoCustomer' => [
        'Singular' => '1' , 
        'Coletiva' => '2' ,
    ],

];
