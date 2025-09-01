<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Form</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <style>
    .button-margin {
      margin-bottom: 20px;
    }
    .tab-content-margin {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Testes PL v2 e Indisponibilidades</h1>
    <hr>
    <div class="row">
      <div class="col-md-12">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="testes-pl-tab" data-toggle="tab" href="#testes-pl" role="tab" aria-controls="testes-pl" aria-selected="true">Testes PL</a>
          </li>
          <li class="nav-item">
            <a class="nav-link " id="indisponibilidades-tab" data-toggle="tab" href="#indisponibilidades" role="tab" aria-controls="indisponibilidades" aria-selected="false">Indisponibilidades</a>
          </li>
        </ul>
        <div class="tab-content tab-content-margin" id="myTabContent">
          <div class="tab-pane fade   show active" id="testes-pl" role="tabpanel" aria-labelledby="testes-pl-tab">
            <div class="row">
              @for ($i = 1; $i <= 27; $i++)
                <div class="col-md-12">
                  <button class="btn btn-primary button-margin" onclick="displayForm({{$i}}, 1)">
                    Teste nº {{$i}}<br>
                    <small>{{$namesPL[$i]}}</small>
                    
                  </button>
                  <div id="form-{{$i}}-1" style="display: none;"></div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-md-6">
                          <h5>Request {{$i}}-1</h5>
                          <div id="request-log-{{$i}}-1"  class="col-md-12">
                            <textarea id="request-log-textarea-{{ $i }}-1" class="form-control"></textarea>
                        </div>
                        </div>
                        <div class="col-md-6">
                         
                          <h5>Response {{$i}}-1</h5>
                          <div id="response-log-{{ $i }}-1" class="col-md-12">
                            <textarea id="response-log-textarea-{{ $i }}-1" class="form-control"></textarea>
                            </div>
                        </div>
                
                      </div>
                      <hr>
                    </div>
                  </div>
                </div>
              @endfor
            </div>
          </div>
          <div class="tab-pane fade " id="indisponibilidades" role="tabpanel" aria-labelledby="indisponibilidades-tab">
            <div class="row">
              @for ($i = 1; $i <= 26; $i++)
                <div class="col-md-12">
                  <button class="btn btn-primary button-margin" onclick="displayForm({{$i}}, 2)">
                    Teste nº {{$i}}<br>
                    <small>{{$namesIndisponibilidades[$i]}}</small>
                   
                </button>
                <div id="form-{{$i}}-2" style="display: none;"></div>
                <div class="row">
                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-md-6">
                          <h5>Request request-log-textarea-{{$i}}-2 </h5>
                          <div id="request-log-{{$i}}-2" class="col-md-12">
                            <textarea id="request-log-textarea-{{ $i }}-2" class="form-control"></textarea>
                        </div>
                        </div>
                        <div class="col-md-6">
                         
                          <h5>Response {{$i}}-2</h5>
                          {{-- <div id="response-log-{{$i}}-2"></div> --}}
                          <div id="response-log-{{ $i }}-2" class="col-md-12">
                            <textarea id="response-log-textarea-{{ $i }}-2" class="form-control"></textarea>
                            </div>
                        </div>
                  
                      </div>
                      <hr>
                    </div>
                  </div>
                </div>
                
              @endfor
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr>
   
  </div>

  <script src="{{ asset('https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js') }}"></script>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
 
 <script>

      function displayForm(buttonNumber, tabPosition) 
      {
        
        console.log('CALL displayForm');

        var formHtml = '';

        switch (tabPosition) {
            case 1: //PL
              console.log('CALL getFormPL');
              formHtml = getFormPL(buttonNumber, tabPosition);
              break;
            case 2: //indisponibilidades
              console.log('CALL getFormIndisponibilidades');
              formHtml = getFormIndisponibilidades(buttonNumber, tabPosition);
              break;
            default:
              console.log('CALL getForm empty');
              formHtml = '';
              break;
        }

        var formElement = document.getElementById('form-' + buttonNumber + '-' + tabPosition);
        if (formElement.style.display === 'block') {
            formElement.style.display = 'none';
        } else {
            formElement.innerHTML = formHtml;
            formElement.style.display = 'block';
        }

      }

      function getFormIndisponibilidades(buttonNumber, tabPosition) 
      {
        var formHtml = '';
        var pspCode = '<?php echo $pspcode; ?>';
        var minhaRota = '{{ route('testes.v2.post') }}';
        switch (buttonNumber) 
        {
            case 1:
            case 4:
            case 6:
            case 7:
            case 8:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description" name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required></textarea>
                      </div>
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                      </div>
                  </fieldset>
                  <div class="text-right">
                      <button type="submit" class="btn btn-success"  >Enviar pedido de teste ${buttonNumber}</button>

                      
                  </div>
              </form>
              `;
              break;
            case 2:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                      <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="" placeholder="Digite o Identificador da indisponibilidade"  required>
                      </div>
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required ></textarea>
                      </div>
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date"  name="start_date" required>
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date"  required>
                      </div>

                    
                      </fieldset>
                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            case 3:
            case 9:
            case 10:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                      <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="" placeholder="Digite o Identificador da indisponibilidade"  required>
                      </div>
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required ></textarea>
                      </div>
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date"  name="start_date" >
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date"  >
                      </div>

                      <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">Selecione um status</option>
                            <option value="0" selected>Cancelada</option>
                            <option value="1">Criada</option>
                            <option value="2">Em progresso</option>
                            <option value="3">Finalizada</option>
                        </select>
                      </div>
                    
                      <div class="form-group">
                          <label for="real_startDate">Data Real início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="real_startDate"  name="real_startDate" >
                      </div>
                      <div class="form-group">
                          <label for="real_endDate">Data Real fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="real_endDate" name="real_endDate" >
                      </div>
                      </fieldset>
                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            case 13:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                       <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="" placeholder="Digite o Identificador da indisponibilidade"  required>
                      </div>

                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required ></textarea>
                      </div>
                   
                      <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">Selecione um status</option>
                            <option value="0" >Cancelada</option>
                            <option value="1">Criada</option>
                            <option value="2">Em progresso</option>
                            <option value="3" selected>Finalizada</option>
                        </select>
                      </div>
                    
                  
                      <div class="form-group">
                          <label for="real_endDate">Data Real fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="real_endDate" name="real_endDate" >
                      </div>
                      </fieldset>
                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            case 14:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                       <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="" placeholder="Digite o Identificador da indisponibilidade"  required>
                      </div>
                      
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required ></textarea>
                      </div>
                   
                      <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">Selecione um status</option>
                            <option value="0" >Cancelada</option>
                            <option value="1">Criada</option>
                            <option value="2">Em progresso</option>
                            <option value="3" selected>Finalizada</option>
                        </select>
                      </div>
                    
                  
                       <div class="form-group">
                          <label for="real_startDate">Data Real início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="real_startDate"  name="real_startDate" >
                      </div>

                       <div class="form-group">
                          <label for="real_endDate">Data Real fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="real_endDate" name="real_endDate" >
                      </div>

                      </fieldset>
                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;  
            case 5:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                      <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="" placeholder="Digite o Identificador da indisponibilidade"  required>
                      </div>
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required ></textarea>
                      </div>
                      <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">Selecione um status</option>
                            <option value="0" selected>Cancelada</option>
                            <option value="1">Criada</option>
                            <option value="2">Em progresso</option>
                            <option value="3" >Finalizada</option>
                        </select>
                      </div>
                
                      </fieldset>
                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            case 11:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="9999" placeholder="Digite o código de PSP" readonly required>
                      </div>
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description" name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required></textarea>
                      </div>
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                      </div>
                  </fieldset>
                  <div class="text-right">
                      <button type="submit" class="btn btn-success"  >Enviar pedido de teste ${buttonNumber}</button>

                      
                  </div>
              </form>
              `;
              break;
              case 12:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description" name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required></textarea>
                      </div>
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                      </div>
                  </fieldset>
                  <div class="text-right">
                      <button type="submit" class="btn btn-success"  >Enviar pedido de teste ${buttonNumber}</button>

                      
                  </div>
              </form>
              `;
              break;
            case 15:
            case 16:
            case 19:
            case 20:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255" required ></textarea>
                      </div>
                      </fieldset>

                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            case 17:
            case 18:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255"  ></textarea>
                      </div>
                      <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="" placeholder="Digite o Identificador da indisponibilidade"  required>
                      </div>
                      <label for="is_finished">
                          <input type="checkbox" id="is_finished" name="is_finished" checked>
                          Finalizada ?
                      </label><br>
                      </fieldset>

                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            case 21:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                        <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="9999" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                      <div class="form-group">
                          <label for="description">Descrição da Indisponibilidade:</label>
                          <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255"  ></textarea>
                      </div>
                    
                      </fieldset>

                      <div class="text-right">
                          <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                      </div>
                
              </form>
              `;
              break;
            
            case 22:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition})";return false;>
                    @csrf
                    <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                    <fieldset>
                        <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                        <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                         <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                        <div class="form-group">
                            <label for="description">Descrição da Indisponibilidade:</label>
                            <textarea class="form-control" id="description"  name="description" placeholder="Digite a descrição da indisponibilidade" maxlength="255"  ></textarea>
                        </div>
                           <div class="form-group">
                          <label for="unavailability_id">Id da indisponibilidade:</label>
                          <input type="text" class="form-control" id="unavailability_id" name="unavailability_id" value="999991" placeholder="Digite o Identificador da indisponibilidade"  readonly required>
                      </div>
                      <label for="is_finished">
                          <input type="checkbox" id="is_finished" name="is_finished" checked>
                          Finalizada ?
                      </label><br>
                      
                        </fieldset>

                        <div class="text-right">
                            <button type="submit" class="btn btn-success">Enviar pedido de teste ${buttonNumber}</button>
                        </div>
                  
                </form>
                `;
              break;
            

            case 23:
            case 24:
            case 25:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                      </div>
                      <label for="is_in_progress">
                          <input type="checkbox" id="is_in_progress[]" name="is_in_progress[]">
                          Em progresso
                      </label><br>
                  </fieldset>
                  <div class="text-right">
                      <button type="submit" class="btn btn-success"  >Enviar pedido de teste ${buttonNumber}</button>

                      
                  </div>
              </form>
              `;
              break;
            case 26:
              formHtml = `
              <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab ${tabPosition}, Button ${buttonNumber}</h4>
                  <fieldset>
                      <input type="hidden" class="form-control" id="tabPosition" name="tabPosition" value="${tabPosition}" readonly required>
                      <input type="hidden" class="form-control" id="tabPosition" name="testNumber" value="${buttonNumber}" readonly required>
                      <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="9999" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    
                      <div class="form-group">
                          <label for="start_date">Data início indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                      </div>
                      <div class="form-group">
                          <label for="end_date">Data fim indisponibilidade:</label>
                          <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                      </div>
                      <label for="is_in_progress">
                          <input type="checkbox" id="is_in_progress" name="is_in_progress">
                          Em progresso
                      </label><br>
                  </fieldset>
                  <div class="text-right">
                      <button type="submit" class="btn btn-success"  >Enviar pedido de teste ${buttonNumber}</button>

                      
                  </div>
              </form>
              `;
              break;
            default:
              formHtml = `
                  <form>
                  <div class="form-group">
                      <label for="other2">Other:</label>
                      <input type="text" class="form-control" id="other2" placeholder="Enter other">
                  </div>
                  <div class="text-right">
                      <button type="submit" class="btn btn-success">Submit</button>
                  </div>
                  </form>
              `;
            break;
        }
        return formHtml;
      }

      function getFormPL(buttonNumber, tabPosition) 
      {
        var formHtml = '';
        var pspCode = '<?php echo $pspcode; ?>';
        var telemovel = '<?php echo $telemovel; ?>';
        var iban = '<?php echo $iban; ?>';
        var iban2 = '<?php echo $iban2; ?>';
        var ibanempresa1 = '<?php echo $ibanempresa1; ?>';
        var ibanempresa2 = '<?php echo $ibanempresa2; ?>';
        var nif = '<?php echo $nif; ?>';
        var nif2 = '<?php echo $nif2; ?>';
        var nipc = '<?php echo $nipc; ?>';
        var nifnaoconforme = '<?php echo $nifnaoconforme; ?>';
        var nifinvalido = '<?php echo $nifinvalido; ?>';
        var niftipo45 = '<?php echo $niftipo45; ?>';
        var listaibans = '<?php echo $listaibans; ?>';
        var ibantipo45 = '<?php echo $ibantipo45; ?>';
        var phonebook = '<?php echo $phone_book; ?>';

        switch (buttonNumber) {
            case 1:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : 'checked' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemóvel para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${telemovel}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
 
            case 2:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                  
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : 'checked' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
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
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : 'checked' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIPC para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nipc}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIPC</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nipc}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              
                break;
            
          
            case 3:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                  
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                        </label>
                    </div>
                </div>

                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nif}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
                break;

            case 5:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                  
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                        </label>
                    </div>
                </div>

                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nif2}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
            
            case 6:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                  
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                        </label>
                    </div>
                </div>

                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nifnaoconforme}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
   
            case 7:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                  
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                        </label>
                    </div>
                </div>

                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nifinvalido}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;


            case 8:
                formHtml = `
                  <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                    @csrf
                    <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                    <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    <div class="form-group row justify-content-left">
                    
                      <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                          </label>
                      </div>
                  </div>

                  <div class="form-group row justify-content-left">
                      <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                      <div class="col-md-6">
                          <label class="radio-inline">
                              <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                          </label>
                      </div>
                  </div>
                  <div class="form-group row justify-content-left">
                      <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                      <div class="col-md-12 ">
                          <input id="identificador" type="text" class="form-control text-center" name="identificador"
                          value="" readonly>
                      </div>
                  </div>    
                  <div class="form-group row justify-content-left">
                      <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                      <div class="col-md-12 ">
                          <input id="iban" type="text" class="form-control text-center" name="iban"
                          value="${iban}"  required>
                      </div>
                  </div>    
                  <div class="form-group row justify-content-left">
                      <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                      <div class="col-md-12 ">
                          <input id="nif" type="text" class="form-control text-center" name="nif"
                          value="${nif}" required >
                      </div>
                  </div>    
                
                  <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                  </form>

                  `;
                break;

            case 9:
                formHtml = `
                  <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                    @csrf
                    <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                    <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
                    <div class="form-group row justify-content-left">
                    
                      <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                      <div class="col-md-6">
                        <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                          </label>
                      </div>
                  </div>

                  <div class="form-group row justify-content-left">
                      <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                      <div class="col-md-6">
                          <label class="radio-inline">
                              <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                          </label>
                          <label class="radio-inline">
                              <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                          </label>
                      </div>
                  </div>
                  <div class="form-group row justify-content-left">
                      <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                      <div class="col-md-12 ">
                          <input id="identificador" type="text" class="form-control text-center" name="identificador"
                          value="${niftipo45}" required>
                      </div>
                  </div>    
                  <div class="form-group row justify-content-left">
                      <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                      <div class="col-md-12 ">
                          <input id="iban" type="text" class="form-control text-center" name="iban"
                          value="${ibantipo45}"  required>
                      </div>
                  </div>    
                  <div class="form-group row justify-content-left">
                      <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                      <div class="col-md-12 ">
                          <input id="nif" type="text" class="form-control text-center" name="nif"
                          value="${niftipo45}" required >
                      </div>
                  </div>    
                
                  <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                  </form>

                  `;
             break;

            case 10:
                formHtml = `
                  <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                    @csrf
                    <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                    <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
             
                  <div class="form-group row justify-content-left">
                      <label for="identificador" class="col-md-3 control-label font-weight-bold" >Identificador do cliente</label>
                      <div class="col-md-12 ">
                          <input id="identificador" type="text" class="form-control text-center" name="identificador"
                          value="${nif}" required>
                      </div>
                  </div>    

                  <div class="form-group row justify-content-left">
                      <label for="" class="col-md-3 control-label font-weight-bold" >Lista IBANs (separados por ",")</label>
                      <div class="col-md-12 ">
                          <input id="iban" type="text" class="form-control text-center" name="iban"
                          value="${listaibans}"  required>
                      </div>
                  </div>    
                
                  <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                  </form>

                  `;
                break;     
                
            case 11:
                formHtml = `
                  <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                    @csrf
                    <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                    <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
             
          
                  <div class="form-group row justify-content-left">
                      <label for="" class="col-md-3 control-label font-weight-bold" >Lista NIFS (separados por ",")</label>
                      <div class="col-md-12 ">
                          <input id="iban" type="text" class="form-control text-center" name="iban"
                          value="${phonebook}"  required>
                      </div>
                  </div>    
                
                  <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                  </form>

                  `;
                break;

            case 12:
                formHtml = `
                  <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                    @csrf
                    <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                    <div class="form-group">
                          <label for="psp_code">Código de PSP:</label>
                          <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                      </div>
             
                <div class="form-group row justify-content-left">
                      <label for="identificador" class="col-md-3 control-label font-weight-bold" >Identificador do cliente</label>
                      <div class="col-md-12 ">
                          <input id="identificador" type="text" class="form-control text-center" name="identificador"
                          value="${nif}" required>
                      </div>
                  </div>    
                  
                
                  <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                  </form>

                  `;
                break;

            case 13:
            case 14:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
              

                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para dissociar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nif}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
                break;
          
            case 15:
              formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : 'checked' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemóvel para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${telemovel}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
 
            case 16:
                formHtml = `
                <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : 'checked' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemóvel para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${telemovel}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban2}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
 
            case 17:
               formHtml = `
                 <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : 'checked' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
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
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : 'checked' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIPC para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nipc}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${ibanempresa1}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIPC</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nipc}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;

            case 18:
               formHtml = `
                 <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : 'checked' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
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
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : 'checked' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIPC para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nipc}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${ibanempresa2}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIPC</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nipc}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
 
            case 19:
               formHtml = `
                 <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : '' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : 'checked' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >NIF para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${nif}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban2}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;

            case 21:
               formHtml = `
                 <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : 'checked' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemovel para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${telemovel}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif2}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;
            
            case 23:
            case 27:
               formHtml = `
                 <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : 'checked' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemovel para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${telemovel}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif}" required >
                    </div>
                </div>    
               <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >Correlation ID origin</label>
                    <div class="col-md-12 ">
                        <input id="correlation_id_origin" type="text" class="form-control text-center" name="correlation_id_origin"
                        value="" required >
                    </div>
                </div>    
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;

            case 24:
               formHtml = `
                 <form onsubmit="callTest(${buttonNumber}, ${tabPosition}); return false;">
                  @csrf
                  <h4>Tab  ${tabPosition}, Button ${buttonNumber}</h4>
                  <div class="form-group">
                        <label for="psp_code">Código de PSP:</label>
                        <input type="text" class="form-control" id="psp_code" name="psp_code" value="${pspCode}" placeholder="Digite o código de PSP" readonly required>
                    </div>
                  <div class="form-group row justify-content-left">
                    <label for="tipoidentificador" class="col-md-3 control-label font-weight-bold"> Tipo de Identificador</label>
                    <div class="col-md-6">
                      <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador0" value="0" {{ ( 0 == Request::old('tipoidentificador0', 0)) ? 'checked' : '' }}> Sem identificador 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador1" value="1" {{ ( 1 == Request::old('tipoidentificador1', 0)) ? 'checked' : 'checked' }}> Telemóvel 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador2" value="2" {{ ( 2 == Request::old('tipoidentificador2', 0)) ? 'checked' : '' }}> NIPC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoidentificador[]" id="tipoidentificador3" value="3" {{ ( 3 == Request::old('tipoidentificador3', 0)) ? 'checked' : '' }}> NIF
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="tipocustomer" class="col-md-3 control-label font-weight-bold"> Tipo de Cliente</label>
                    <div class="col-md-6">
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer1" value="1" {{ ( 1 == Request::old('tipocustomer1', 0)) ? 'checked' : 'checked' }}> Particular 
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipocustomer[]" id="tipocustomer2" value="2" {{ ( 2 == Request::old('tipocustomer2', 0)) ? 'checked' : '' }}> Empresa
                        </label>
                    </div>
                </div>
                <div class="form-group row justify-content-left">
                    <label for="identificador" class="col-md-3 control-label font-weight-bold" >Telemovel para associar</label>
                    <div class="col-md-12 ">
                        <input id="identificador" type="text" class="form-control text-center" name="identificador"
                        value="${telemovel}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >IBAN</label>
                    <div class="col-md-12 ">
                        <input id="iban" type="text" class="form-control text-center" name="iban"
                        value="${iban2}"  required>
                    </div>
                </div>    
                <div class="form-group row justify-content-left">
                    <label for="" class="col-md-3 control-label font-weight-bold" >NIF</label>
                    <div class="col-md-12 ">
                        <input id="nif" type="text" class="form-control text-center" name="nif"
                        value="${nif2}" required >
                    </div>
                </div>    
              
                <button type="submit" class="btn btn-primary float-right ml-2" > Enviar pedido de teste ${buttonNumber} <i class="fas fa-save"></i> </button>
                </form>

                `;
              break;


           default:
              formHtml = `
                <form>
                    <h4>Tab 000, Button ${buttonNumber}</h4>
                <div class="form-group">
                    <label for="other">Other:</label>
                    <input type="text" class="form-control" id="other" placeholder="Enter other">
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
                </form>
                `;
              break;
        }
        return formHtml;
      }

      function getDataIndisponibilidades(testNumber, tabPosition)
      {
        var pspCode = '';
        var description = '';
        var unavailability_id = '';
        var status = '';
        var startDate = '';
        var endDate = '';
        var real_startDate = '';
        var real_endDate = '';
        var is_in_progress = 'false';
        var is_finished = 'false';

        //check for nulls elements
        var element = document.getElementById('psp_code');
        if (element) { 
          pspCode = document.getElementById('psp_code').value;
        }
        element = document.getElementById('description');
        if (element) { 
          description = document.getElementById('description').value;
        }
        element = document.getElementById('unavailability_id');
        if (element) { 
          unavailability_id = document.getElementById('unavailability_id').value;
        }
        element = document.getElementById('status');
        if (element) { 
          status = document.getElementById('status').value;
        }
        element = document.getElementById('start_date');
        if (element) { 
          startDate = document.getElementById('start_date').value;
        }
        element = document.getElementById('end_date');
        if (element) { 
          endDate = document.getElementById('end_date').value;
        }
        element = document.getElementById('real_startDate');
        if (element) { 
          real_startDate = document.getElementById('real_startDate').value;
        }
        element = document.getElementById('real_endDate');
        if (element) { 
          real_endDate = document.getElementById('real_endDate').value;
        }
        element = document.getElementById('is_in_progress');
        if (element) { 
          is_in_progress = $('#is_in_progress').is(':checked') // true or false
          console.log('is_in_progress :' + is_in_progress);
          is_in_progress = document.getElementById('is_in_progress').checked;
          console.log('is_in_progress :' + is_in_progress);
        }
        element = document.getElementById('is_finished');
        if (element) { 
          is_finished = $('#is_finished').is(':checked') // true or false
          console.log('is_finished :' + is_finished);
          is_finished = document.getElementById('is_finished').checked;
          console.log('is_finished :' + is_finished);
        }

        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        
           
        const dataObject = {
          testNumber: testNumber,
          tabPosition: tabPosition,
          pspCode: pspCode,
          unavailability_id: unavailability_id,
          description: description,
          startDate: startDate,
          endDate: endDate,
          real_startDate: real_startDate,
          real_endDate: real_endDate,
          status: status,
          is_in_progress: is_in_progress,
          is_finished:is_finished,
          method: 'callTestIndisponibilidades', 
          csrf_token: CSRF_TOKEN // Use 'csrf_token' as the key      
        } 

        return dataObject;


      }

      function getDataPL(testNumber, tabPosition)
      {

        var pspCode = '';
        var tipoidentificador ='';
        var tipocustomer = '';
        var identificador ='';
        var iban = '';
        var nif = '';
        var correlation_id_origin = '';
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        
        var element = document.getElementById('identificador');
        if (element) { 
          identificador = document.getElementById('identificador').value;
        }
        element = document.querySelector('input[name="tipoidentificador[]"]:checked');
        if (element) { 
          tipoidentificador =  document.querySelector('input[name="tipoidentificador[]"]:checked').value;
        }
        element = document.querySelector('input[name="tipocustomer[]"]:checked');
        if (element) { 
          tipocustomer = document.querySelector('input[name="tipocustomer[]"]:checked').value;
        }
        element = document.getElementById('nif');
        if (element) { 
          nif = document.getElementById('nif').value;
        }
        element = document.getElementById('iban');
        if (element) { 
          iban = document.getElementById('iban').value;
        }
     
        element = document.getElementById('psp_code');
        if (element) { 
          pspCode = document.getElementById('psp_code').value;
        }

        element = document.getElementById('correlation_id_origin');
        if (element) { 
          correlation_id_origin = document.getElementById('correlation_id_origin').value;
        }


        const dataObject = {
          testNumber: testNumber,
          tabPosition: tabPosition,
          pspCode: pspCode,
          tipocustomer:tipocustomer,
          tipoidentificador: tipoidentificador,
          identificador: identificador,
          iban: iban,
          nif: nif,
          correlation_id_origin:correlation_id_origin,
          method: 'callTestPL', 
          csrf_token: CSRF_TOKEN // Use 'csrf_token' as the key      
        } 

        console.log(dataObject);

        return dataObject;

      }



      function callTest(testNumber, tabPosition) 
      {
        console.log('CALL TEST');
        try {

          //fill data for post
          var dataArray = [];
          switch (tabPosition) {
            case 1: //PL
              dataArray = getDataPL(testNumber, tabPosition);
              break;
            case 2: //indisponibilidades
              dataArray = getDataIndisponibilidades(testNumber, tabPosition);
              break;
            default:
              dataArray = [];
              break;
          }
          console.log(dataArray);

          //execute ajax call 
          fetch('{{ URL::route('testes.v2.post') }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json'
              },
              body: JSON.stringify(dataArray) 
          })
          .then(response => response.json())
          .then(data => {
              console.log('Tab position:', tabPosition);
              console.log('Test number:', testNumber);
              console.log('Data:', data);
              // var responseText = 'Request:' + JSON.stringify(data, null, 2) ;
              var responseText = data['data'];
              var responseRequestText = data['response'] ;

              if(tabPosition === 1) {
                  var textarea = document.getElementById('request-log-textarea-' + testNumber + '-1');
                  if (textarea) {
                      textarea.innerHTML = responseText;
                  } else {
                      console.error('Elemento textarea não encontrado');
                  }
                  var textarea1 = document.getElementById('response-log-textarea-' + testNumber + '-1');       
                  if (textarea1) {
                      textarea1.innerHTML = responseRequestText;
                  } else {
                      console.error('Elemento textarea1 não encontrado');
                  }
              }else {
                  var textarea3 = document.getElementById('request-log-textarea-' + testNumber + '-2');       
                  if (textarea3) {
                      textarea3.innerHTML = responseText;
                  } else {
                      console.error('Elemento textarea 3 não encontrado');
                  }
                  var textarea2 = document.getElementById('response-log-textarea-' + testNumber + '-2');       
                  if (textarea2) {
                      textarea2.innerHTML = responseRequestText;
                      textarea2.focus();
                  } else {
                      console.error('Elemento textarea2 não encontrado');
                  }
              }

              return;
              
          })
          .catch(error => {
              console.error('Erro:', error);
              if(tabPosition == 1) {
                  document.getElementById('response-log-textarea-' + testNumber +'-1').innerHTML = 'Response: ' + JSON.stringify(error);
              }else {
                  document.getElementById('response-log-textarea-' + testNumber +'-2').innerHTML = 'Response: ' + JSON.stringify(error);
              }

          });

          
        } catch (error) {
            console.error('Erro:', error);
        }

    }
  </script>
</body>
</html>