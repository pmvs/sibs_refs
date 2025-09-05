@extends('layouts.plain')
@section('title','Resultado SIBS')
@section('content')
<div class="container" style="max-width:720px">
  <h1>Resultado da Consulta SIBS</h1>

  <p><strong>Entity:</strong> {{ $input['entity'] }}</p>
  <p><strong>Reference:</strong> {{ $input['reference'] }}</p>

  @if (($result['ok'] ?? false) === true)
    <div class="card mt-3">
      <div class="card-body">
        <h5>Payment Owner</h5>
        <p>{{ $result['data']['paymentOwnerName'] ?? '' }}</p>

        <h5 class="mt-3">Subcomerciante</h5>
        <p>{{ $result['data']['subMerchantName'] ?? '' }}</p>
      </div>
    </div>
  @else
    <div class="alert alert-danger mt-3">
      <strong>Erro:</strong> {{ $result['error'] ?? 'Falha' }}  
      ({{ $result['code'] ?? 'UNKNOWN' }})
    </div>
  @endif

  <a href="{{ url('/debug/sibs') }}" class="btn btn-secondary mt-3">Nova consulta</a>
</div>
@endsection
