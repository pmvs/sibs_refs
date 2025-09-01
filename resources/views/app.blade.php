<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        // session_cache_limiter('nocache');
        //set headers to NOT cache a page
        //header("Cache-Control: no-cache"); //HTTP 1.1
         //set headers to NOT cache a page
        // header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
        // // header("Pragma: no-cache"); //HTTP 1.0
        // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        // header("Pragma: no-cache"); //HTTP 1.0
        // header("Expires: 0"); // Date in the past

        header("Content-Type: application/json");
        header("Expires: on, 01 Jan 1970 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

    @endphp 

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Caixa de Crédito') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <!-- Scripts -->
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> 
    <script src="{{ asset('js/jquery.min.js') }}"></script> 

    <!-- Styles -->    
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" type="text/css" > --}}

    {{-- <link rel="stylesheet" href="{{ asset('css/fixedHeader.bootstrap.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap.min.css') }}"> --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/buttons.dataTables.min.css') }}"> --}}

   {{-- <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>  --}}
   {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/fontawesome.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.0-2/css/all.min.css" /> 

     --}}

   {{-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css "> --}}
   {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css "> --}}
   {{-- <script src="{{ asset('js/dataTables.bootstrap.min.js') }}"></script> 
 --}}


   {{-- <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.5/css/fixedHeader.bootstrap.min.css "> --}}
   {{-- <link rel="stylesheet" href="{{ asset('css/fixedHeader.bootstrap.min.css') }}" /> 

   <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">

   <link href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" rel="stylesheet">
   <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap.min.css" rel="stylesheet"> --}}

    {{-- <script src="{{ asset('js/datatables.min.js') }}"></script> 
    <script src="{{ asset('js/dataTables.bootstrap.min.js') }}"></script> 
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>  --}}
    {{-- <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>  --}}
{{-- 
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/dataTables.jqueryui.min.css"/>
  
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.15/js/dataTables.jqueryui.min.js"></script>  --}}

{{--     <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap.min.js') }}"></script> 
    <script src="{{ asset('js/dataTables.fixedHeader.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('js/responsive.bootstrap.min.js') }}"></script> 
    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script> 
     --}}
     {{-- <script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
     <script src="https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
     
      --}}
</head>
<body>
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @yield('js')
</body>
</html>
