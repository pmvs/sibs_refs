@extends('layouts.app')

@section('content')
<div class="container" style="max-width:720px">
  <h1>Debug SIBS – Consulta Owner/Subcomerciante</h1>

  <form method="POST" action="{{ url('/debug/sibs') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Entity</label>
      <input type="text" name="entity" class="form-control" value="{{ old('entity','12345') }}" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Reference</label>
      <input type="text" name="reference" class="form-control" value="{{ old('reference','555666777') }}" required>
    </div>
    <button type="submit" class="btn btn-primary">Testar SIBS</button>
  </form>
</div>
@endsection
