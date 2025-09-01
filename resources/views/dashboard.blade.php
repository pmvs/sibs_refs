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

<div class="card-header"><h2>COPS</h2></div>
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
<hr>


<div class="card-header"><h2>Confirmation & Lookup</h2></div>
<form id="teste" method="POST" action={{ route('testes.post') }} class="form-vertical" novalidate >
    @csrf
    <div class="form-group row justify-content-left">
        <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="tipoidentificador1[]" id="tipoidentificador3" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
            </label>
            <label class="radio-inline">
                <input type="radio" name="tipoidentificador1[]" id="tipoidentificador4" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
            </label>
        </div>
    </div>
      
    <div class="form-group row justify-content-left">
        <label for="" class="col-md-3 control-label font-weight-bold" >Identificador para obter IBAN</label>
        <div class="col-md-12">
            <input id="identificador1" type="text" class="form-control text-center" name="identificador1"
            value=""  >
        </div>
    </div>    
    <div class="form-group row justify-content-left">
        <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
        <div class="col-md-12 ">
            <input id="iban" type="text" class="form-control text-center" name="iban"
            value=""  >
        </div>
    </div>    
    <button type="submit" class="btn btn-primary float-right ml-2" > Enviar <i class="fas fa-save"></i> </button>
</form>

<hr>
<div class="card-header"><h2>Associar / Dissociar PL</h2></div>
<form id="teste2" method="POST" action={{ route('testes.associar.post') }} class="form-vertical" novalidate >
    @csrf
    <div class="form-group row justify-content-left">
        <label for="acao" class="col-md-3 control-label font-weight-bold"> Ação</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="acao[]" id="insert" value="1" {{ ( 1 == Request::old('insert', 0)) ? 'checked' : '' }}> Insert 
            </label>
            <label class="radio-inline">
                <input type="radio" name="acao[]" id="delete" value="2" {{ ( 2 == Request::old('delete', 0)) ? 'checked' : '' }}> Delete
            </label>
        </div>
    </div>
    <div class="form-group row justify-content-left">
        <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
            </label>
            <label class="radio-inline">
                <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
            </label>
        </div>
    </div>
      
    <div class="form-group row justify-content-left">
        <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : '' }}> Particular 
            </label>
            <label class="radio-inline">
                <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
            </label>
        </div>
    </div>
    <div class="form-group row justify-content-left">
        <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemóvel/NIF para associar</label>
        <div class="col-md-12 ">
            <input id="identificador" type="text" class="form-control text-center" name="identificador"
            value=""  >
        </div>
    </div>    
    <div class="form-group row justify-content-left">
        <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
        <div class="col-md-12 ">
            <input id="iban2" type="text" class="form-control text-center" name="iban2"
            value=""  >
        </div>
    </div>    
    <div class="form-group row justify-content-left">
        <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
        <div class="col-md-12 ">
            <input id="nif" type="text" class="form-control text-center" name="nif"
            value=""  >
        </div>
    </div>    
   
    <button type="submit" class="btn btn-primary float-right ml-2" > Enviar <i class="fas fa-save"></i> </button>
</form>
<hr>

<div class="card-header"><h2>Reativar/Eliminar associações pendentes</h2></div>
<form id="teste24" method="POST" action={{ route('testes.post') }} class="form-vertical" novalidate >
    @csrf
    <div class="form-group row justify-content-left">
        <label for="acao" class="col-md-3 control-label font-weight-bold"> Ação</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="acao2[]" id="reativar" value="1" {{ ( 1 == Request::old('reativar', 0)) ? 'checked' : '' }}> Reativar
            </label>
            <label class="radio-inline">
                <input type="radio" name="acao2[]" id="eliminar" value="2" {{ ( 2 == Request::old('eliminar', 0)) ? 'checked' : '' }}> Eliminar
            </label>
        </div>
    </div>
    <div class="form-group row justify-content-left">
        <label for="tipoidentificador_pendente" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="tipoidentificador_pendente[]" id="tipoidentificador5" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
            </label>
            <label class="radio-inline">
                <input type="radio" name="tipoidentificador_pendente[]" id="tipoidentificador6" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
            </label>
        </div>
    </div>
      
    <div class="form-group row justify-content-left">
        <label for="tipocustomer_pendente" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
        <div class="col-md-6">
            <label class="radio-inline">
                <input type="radio" name="tipocustomer_pendente[]" id="tipocustomer5" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : '' }}> Particular 
            </label>
            <label class="radio-inline">
                <input type="radio" name="tipocustomer_pendente[]" id="tipocustomer6" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
            </label>
        </div>
    </div>
    <div class="form-group row justify-content-left">
        <label for="identificador_pendente" class="col-md-3 control-label font-weight-bold" >Telemóvel/NIF para associar</label>
        <div class="col-md-12 ">
            <input id="identificador_pendente" type="text" class="form-control text-center" name="identificador_pendente"
            value=""  >
        </div>
    </div>    
    <div class="form-group row justify-content-left">
        <label for="iban_pendente" class="col-md-3 control-label font-weight-bold" >IBAN</label>
        <div class="col-md-12 ">
            <input id="iban_pendente" type="text" class="form-control text-center" name="iban_pendente"
            value=""  >
        </div>
    </div>    
    <div class="form-group row justify-content-left">
        <label for="nif_pendente" class="col-md-3 control-label font-weight-bold" >NIF</label>
        <div class="col-md-12 ">
            <input id="nif_pendente" type="text" class="form-control text-center" name="nif_pendente"
            value=""  >
        </div>
    </div>    
    <div class="form-group row justify-content-left">
        <label for="correlationidorigin" class="col-md-3 control-label font-weight-bold" >Correlation ID original</label>
        <div class="col-md-12 ">
            <input id="correlationidorigin" type="text" class="form-control text-center" name="correlationidorigin"
            value=""  >
        </div>
    </div>    
   
   
    <button type="submit" class="btn btn-primary float-right ml-2" > Enviar <i class="fas fa-save"></i> </button>
</form>
<hr>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
