@extends('app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            {{-- <div class="card">
                <div class="card-header">PLCP</div> --}}

                <div class="card-body">
                  
           
                    {{ print_r($result, true) }}
                    <hr>
                    <div class="card-header"><h2>TESTES CARGA</h2></div>
                    <form id="testecarga" method="POST" action={{ route('testes.carga.pl.post') }} class="form-vertical" novalidate >
                        @csrf
                        <button type="submit" class="btn btn-primary float-right ml-2" > START <i class="fas fa-save"></i> </button>
                    </form>
                    <hr>
                    {{-- <div class="card-header"><h2>COPS</h2></div>
                    <form id="teste1" method="POST" action={{ route('testes.post') }} class="form-vertical" novalidate >
                        @csrf
                        <div class="form-group row justify-content-left">
                            <label for="ibancop" class="col-md-3 control-label font-weight-bold"> Iban para COPS</label>
                            <div class="col-md-6">
                                <input id="ibancop" type="text" value="{{ old('ibancop') }}" class="form-control text-center" name="ibancop">
                            </div>
                        </div>
                    
                        <button type="submit" class="btn btn-primary float-right ml-2" > Enviar <i class="fas fa-save"></i> </button>
                    </form>
                    <hr>
                    <div class="card-header"><h2>COPB</h2></div>
                    <form id="teste3" method="POST" action={{ route('testes.copb') }} class="form-vertical" novalidate >
                        @csrf

                        <div class="form-group row justify-content-left">
                            <label for="psp_destination" class="col-md-12 control-label font-weight-bold"> Banco de Destino para COPB</label>
                            <div class="col-md-3">
                                <input id="psp_destination" type="text" value="{{ old('psp_destination') }}" class="form-control text-center" name="psp_destination">
                            </div>
                        </div>
                    

                        <div class="form-group row justify-content-left">
                            <label for="ambiente" class="col-md-3 control-label font-weight-bold"> Ambiente</label>
                            <div class="col-md-6">
                                <label class="radio-inline">
                                    <input type="radio" name="ambiente[]" id="teste" value="1" {{ ( 1 == Request::old('teste', 1)) ? 'checked' : '' }}> Executar Teste 
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="ambiente[]" id="prod" value="2" {{ ( 2 == Request::old('prod', 2)) ? 'checked' : '' }}> Produção
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label for="nrcopb" class="col-md-12 control-label font-weight-bold"> Nr. COPB</label>
                                <div class="col-md-12">
                                    <input id="nrcopb" type="text" value="{{ old('nrcopb') }}" class="form-control text-center" name="nrcopb">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row justify-content-left">
                            <label for="copb" class="col-md-12 control-label font-weight-bold"> Iban + NIF/NIPC para COPB</label>
                            <div class="col-md-12">
                                <input id="copb" type="text" value="{{ old('copb[]') }}" class="form-control text-center" name="copb[]">
                            </div>
                            <div class="col-md-12">
                                <input id="copb2" type="text" value="{{ old('copb[]]') }}" class="form-control text-center" name="copb[]">
                            </div>
                            <div class="col-md-12">
                                <input id="copb3" type="text" value="{{ old('copb[]]') }}" class="form-control text-center" name="copb[]">
                            </div>
                        </div>
                    
                        <button type="submit" class="btn btn-primary float-right ml-2" > Enviar <i class="fas fa-save"></i> </button>
                    </form>
                    <hr> --}}





                </div>
            </div>
        </div>
    </div>
</div>
@endsection
