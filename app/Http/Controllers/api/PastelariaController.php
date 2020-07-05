<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\GenericaModel;
use Mail;

/**
 * Classe Pastelaria recebe request que deternina com funcionará a API REST
 * {
 *  "module": "",  (cliente,pastel e pedido)
 *  "action": "",  (create,read,update,delete,list)
 * }
 * @author Thiago T Silva <ttsilva.info@gmail.com>
 * @since 04/07/2020
 * @return Json
 * 
 */
class PastelariaController extends Controller {

    var $action = Array();
    var $fields = Array();
    var $emailTo = "";

    public function __construct() {

        //Ações que são permitidas
        $this->action = Array('create', 'read', 'update', 'delete', 'list');

        $this->fields = Array(
            'pastel' => Array('nome', 'url_foto', 'preco'),
            'cliente' => Array('nome', 'email', 'telefone', 'data_nascimento', 'endereco', 'complemento', 'bairro', 'cep'),
            'pedido' => Array('codigo_cliente', 'codigo_pastel', 'preco'),
        );
    }

    /**
     * 
     * Recebe a requisição do tipo GET
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $request Requisição GET 
     * 
     */
    public function index(Request $request) {
        if (is_array($request->all())) {
            $this->selectModuleAction($request->all());
        }
    }

    /**
     * 
     * Recebe a requisição do tipo POST
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $request Requisição POST 
     * 
     */
    public function store(Request $request) {
        if (is_array($request->all())) {
            $this->selectModuleAction($request->all());
        }
    }

    /**
     * Verifica o module (cliente,pastel ou pedido) e direciona para Ação
     * Module determina a tabela 
     * Action determina as seguintes ações (create/inserir, read/select, update, delete, list)
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $request Requisição POST 
     * 
     * 
     * @param $this->index || $this->store $request
     */
    public function selectModuleAction($request) {


        if (isset($request['module'])) {
            $module = $request['module'];
        }
        if (isset($request['action'])) {
            $acao = $request['action'];
        }

        unset($request['module']);
        unset($request['action']);


        if (empty($module) || empty($acao)) {
            die(json_encode(Array('status' => 'error', 'msg' => 'action ou module sao obrigatorios!')));
        }

        if (!empty($module) && !empty($acao)) {

            if (!in_array($acao, $this->action)) {
                die(json_encode(Array('status' => 'error', 'msg' => 'action invalido!')));
            }

            switch ($module) {
                case 'pastel':
                case 'cliente':
                $model = new GenericaModel($module);
                $this->selectAction($acao, $model, $request, $module);
                break;

                case 'pedido':
                $model = new GenericaModel($module);

                    if ($acao == 'create') { // Inserir Pedido
                        $arrPedidos = $this->pedidos($request, $model, $module, $acao);
                        foreach ($arrPedidos as $pedido) {
                            $this->selectAction($acao, $model, $pedido, $module);
                        }
                    }

                    if ($acao == 'update') {
                        $this->verificarAtualizacaoPedido($request, $model);
                    }

                    $this->selectAction($acao, $model, $request, $module);

                    break;

                    default:
                    die(json_encode(Array('status' => 'error', 'msg' => 'module invalido!')));
                    break;
                }
            }
        }

    /**
     * Verifica itens que serão adicionado no Pedido
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $request Requisição POST/GET
     * @param type $model objeto model  
     * @param type $module nome da tabela 
     * @param type $acao  
     * 
     */
    private function pedidos($request, $model, $module, $acao) {

        $this->verificarClienteItens($request, $model);
        foreach ($request['itens'] AS $key => $value) {
            $arrPedidos[] = Array(
                "codigo_cliente" => $request['codigo_cliente'],
                "codigo_pastel" => $value['codigo_pastel'],
            );
        }
        $this->sendEmailPedido($model, $arrPedidos);

        return $arrPedidos;
    }

    /**
     * Verifica itens que serão adicionado no Pedido
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $module nome da tabela  
     * @param type $arrPedidos - Informações cliente e tabela pastel 
     * 
     */
    private function sendEmailPedido($model, $arrPedidos) {

        $arrNew = Array();
        //Agrupar Pedidos 
        foreach ($arrPedidos AS $key => $value) {
            $arrNew['codigo_cliente'] = $value['codigo_cliente'];
            $result = $model->get(array(['id', $value['codigo_pastel']]), 'pastel');
            if (!isset($arrNew['itens'][$value['codigo_pastel']])) {
                $arrNew['itens'][$value['codigo_pastel']] = (Array) $result['data'][0];
                $arrNew['itens'][$value['codigo_pastel']]['qtd'] = 1;
            } else {
                $arrNew['itens'][$value['codigo_pastel']]['qtd'] = 1 + $arrNew['itens'][$value['codigo_pastel']]['qtd'];
            }
        }
        //Informações do Cliente
        $where = array(['id', $arrNew['codigo_cliente']]);
        $resultCliente = $model->get($where, 'cliente');
        $arrNew['cliente'] = (Array) $resultCliente['data'][0];

        //Valor todos do Pedido
        $arrNew['preco_total'] = 0;
        $arrNew['qtd_total'] = 0;
        foreach ($arrNew['itens'] AS $idPastel => $vItens) {
            $arrNew['preco_total'] += $vItens['preco'] * $vItens['qtd'];
            $arrNew['qtd_total'] += $vItens['qtd'];
        }

       
        $this->emailTo = $arrNew['cliente']['email'];


        Mail::send('mail.pedidos', $arrNew, function($m) {
            $m->from('ttsilva.info@gmail.com', 'Pastelaria');
            $m->to($this->emailTo);
            $m->subject('Comprovante de Pedido');
        });
    }

    /**
     * Verificar no momento da atualização do Pedido, se existe o cliente ou o Pastel
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $request - 
     * @param type $model       
     * 
     */
    private function verificarAtualizacaoPedido($request, $model) {

        $resultCliente = $model->get(array(['id', $request['codigo_cliente']]), 'cliente');
        if (count($resultCliente['data']) < 1) {
            die(json_encode(Array('status' => 'error', 'msg' => 'cliente nao existe!')));
        }

        $resultPastel = $model->get(array(['id', $request['codigo_pastel']]), 'pastel');
        if (count($resultPastel['data']) < 1) {
            die(json_encode(Array('status' => 'error', 'msg' => 'Pastel nao existe!')));
        }
    }

    /**
     * Verificar no momento da atualização do Pedido, se existe o cliente ou o Pastel
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $request dados que foram enviado via GET/POST
     * @param type $model - objeto model conforme module definido 
     * 
     */
    private function verificarClienteItens($request, $model) {

        $resultCliente = $model->get(array(['id', $request['codigo_cliente']]), 'cliente');

        if (count($resultCliente['data']) > 0) {

            if (count($request['itens']) > 0) {

                foreach ($request['itens'] AS $key => $vItens) {
                    //Verificar se existe o item
                    $resultPastel = $model->get(array(['id', $vItens['codigo_pastel']]), 'pastel');
                    if (count($resultPastel['data']) < 1) {
                        die(json_encode(Array('status' => 'error', 'msg' => 'ID:' . $vItens['codigo_pastel'] . '  pastel nao existe!')));
                    }
                }
            } else {
                die(json_encode(Array('status' => 'error', 'msg' => 'nao ha itens no pedido!')));
            }
        } else {
            die(json_encode(Array('status' => 'error', 'msg' => 'cliente nao existe!')));
        }
    }

    /**
     * Determina qual Ação que será execultada
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $acao (create/inserir, read/select, update, delete, list)
     * @param type $request dados que foram enviado via GET/POST
     * @param type $model - objeto model conforme module definido 
     * 
     */
    private function selectAction($acao, $model, $request, $module) {

        switch ($acao) {
            case 'read':
            $this->read($model, $request);
            break;
            case 'list':
            $this->list($model);
            break;
            case 'create':
            $this->create($model, $request, $module);
            break;
            case 'update':
            $this->update($model, $request, $module);
            break;
            case 'delete':
            $this->delete($model, $request, $module);
            break;
        }
    }

    /**
     * Deleta conter a funcionalidade de soft deleting
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $model - Objeto Model conforme module definido 
     * @param type $request dados que foram enviado via GET/POST
     * @param type $module - tabela 
     * 
     */
    private function delete($model, $request, $module) {

        if (!empty($request['id'])) {
            $where = array(['id', $request['id']]);
            $result = $model->read($where);

            if (count($result['data']) > 0) {
                if ($model->deleteData($request)) {
                    die(json_encode(Array('status' => 'sucess', 'msg' => 'Registro deletado com sucesso!')));
                }
            } else {
                die(json_encode(Array('status' => 'error', 'msg' => 'Nao houve alteracoes, registro nao existe!')));
            }
        } else {
            die(json_encode(Array('status' => 'error', 'msg' => 'id vazio!')));
        }
    }

    /**
     * Atualizar - Esse método verifica pelo id do module se existe o registo,
     * caso exista o ristro, também será verificado se houve alterações, e se houver a ação update será realiza.
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $model - Objeto Model conforme module definido 
     * @param type $request dados que foram enviado via GET/POST
     * @param type $module - tabela 
     * 
     */
    private function update($model, $request, $module) {

        if (!empty($request['id'])) {
            $where = array(['id', $request['id']]);
            $result = $model->read($where);

            if (count($result['data']) > 0) {
                $arrData = (array) $result['data'][0];
                $id = $request['id'];
                unset($request['id']);
                $arrDiff = array_diff($request, $arrData);

                if (count($arrDiff) > 0) {
                    if ($model->updateData($arrDiff, $id)) {
                        die(json_encode(Array('status' => 'sucess', 'msg' => 'Registro atualizado com sucesso!')));
                    }
                } else {
                    die(json_encode(Array('status' => 'error', 'msg' => 'Nao houve alteracoes!')));
                }
            } else {
                die(json_encode(Array('status' => 'error', 'msg' => 'nao ha registro com esse id!')));
            }
        } else {
            die(json_encode(Array('status' => 'error', 'msg' => 'id vazio!')));
        }
    }

    /**
     * Create/Insert - Insere o registo, coforme o module
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $model - Objeto Model conforme module definido 
     * @param type $request dados que foram enviado via GET/POST
     * @param type $module - tabela 
     * 
     */
    private function create($model, $request, $module) {

        if ($this->validarFields($request, $module)) {
            if ($module == 'cliente') {
                $this->validarEmail($model, $request);
            }
        }

        if ($model->create($request) && $module != 'pedido') {
            die(json_encode(Array('status' => 'sucess', 'msg' => $module . ' inserido com sucesso!')));
        }

        if ($module == 'pedido') { //Inserir mais de uma vez
            $model->create($request);
            echo json_encode(Array('status' => 'sucess', 'msg' => $module . ' inserido com sucesso!'));
        }
    }

    /**
     * list- Lista todos os registros conforme o module
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $model - Objeto Model conforme module definido      
     * 
     */
    private function list($model) {
        $result = $model->list();
        die(json_encode($result));
    }

    /**
     * read - Lista todos os registros filtrando pelo id
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type $model - Objeto Model conforme module definido      
     * @param type $request - Dados da requisição GET/POST    
     * 
     */
    private function read($model, $request) {

        if (!empty($request['id'])) {
            $where = array(['id', $request['id']]);
            $result = $model->read($where);

            if (count($result['data']) > 0) {
                die(json_encode($result));
            } else {
                die(json_encode(Array('status' => 'error', 'msg' => 'nao ha registros com esse id!')));
            }
        } else {
            die(json_encode(Array('status' => 'error', 'msg' => 'id vazio!')));
        }
    }

    /**
     * Valida campos do request conforme, se não há nenhum campo divergente!
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020          
     * @param type $request - Dados da requisição GET/POST    
     * @param type $module - Tabela    
     * 
     */
    private function validarFields($request, $module) {
        $arrNotFields = Array();

        if (is_array($request)) {
            foreach ($request AS $campo => $value) {
                if (!in_array($campo, $this->fields[$module])) {
                    $arrNotFields[] = $campo;
                }
            }
            if (count($arrNotFields) > 0) {
                die(json_encode(Array('status' => 'error', 'msg' => 'ha campos nao permitidos!', 'data' => $arrNotFields)));
            } else {
                return true;
            }
        }
    }

    /**
     * Valida se existe cliente com um e-mail no banco de dados
     * 
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020  
     * @param type $model - Objeto model   
     * @param type $request - Dados da requisição GET/POST           
     * 
     */
    private function validarEmail($model, $request) {

        $where = array(['email', $request['email']], ['deleted_at', null]);
        $result = $model->read($where);
        if (count($result['data']) > 0) {
            die(json_encode(Array('status' => 'error', 'msg' => 'Usuario com o e-mail (' . $request['email'] . '), existente!')));
        } else {
            return true;
        }
    }

}
