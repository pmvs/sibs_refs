<?php
return [
  'sibs_base'=> rtrim(env('SIBS_BASE_URL',''),'/'),
  'sibs_ver' => env('SIBS_VERSION','v1'),
  'sibs_id'  => env('SIBS_CLIENT_ID',''),
  'bearer'   => env('SIBS_BEARER_TOKEN',''),
  'req_timeout'=>(int) env('REQUEST_TIMEOUT',4),
  'con_timeout'=>(int) env('CONNECT_TIMEOUT',2),
];