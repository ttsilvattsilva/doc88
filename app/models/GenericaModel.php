<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * GenericaModel 
 * {
 *  "module": "",  (cliente,pastel e pedido)
 *  "action": "",  (create,read,update,delete,list)
 * }
 * @author Thiago T Silva <ttsilva.info@gmail.com>
 * @since 04/07/2020
 * 
 */
class GenericaModel extends Model {

    var $db = '';

    use SoftDeletes;

    protected $table = ""; // Determina a tabela conforme informacoes da controler

    public function __construct($table) {

        $this->table = $table;

        try {
            $db = env('DB_DATABASE');
            $this->db = DB::connection('mysql');
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e);
        }
    }

    /**
     *  Insere registro conforme request a tabela é determina na controller
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * 
     */
    public function create($resquest) {
        return $this->db->table($this->table)->insert($resquest);
    }

    /**
     * List todos registros da tabela determina na controller
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * 
     */
    public function list() {
        $data = $this->db->table($this->table)
                ->select("*")
                ->whereNull('deleted_at')
                ->get();
        return Array('status' => 'sucess', 'data' => $data);
    }

    /**
     * read - Filtra  consulta por id
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type (Array) $where Filtro
     * @param type $fields campos
     * 
     */
    public function read($where = Array(), $fields = "*") {

        $data = $this->db->table($this->table)
                ->select($fields)
                ->where($where)
                ->whereNull('deleted_at')
                ->get();

        return Array('status' => 'sucess', 'data' => $data);
    }

    /*  updateData - Atualiza registro conforme module determinado na controller
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020
     * @param type (Array) $resquest Filtro
     * @param type $id - id tabela 
     * 
     */

    public function updateData($resquest, $id) {
        return $this->db->table($this->table)
                        ->where("id", $id)
                        ->whereNull('deleted_at')
                        ->update($resquest);
    }

    /*  Delete - conter a funcionalidade de soft deleting
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020     
     * @param type $request
     * 
     */

    public function deleteData($resquest) {
        return $this->find($resquest['id'])->delete();
    }

    /*  get -  Select conforme parametros da controller
     * @author Thiago T Silva <ttsilva.info@gmail.com>
     * @since 04/07/2020     
     * @param type $request
     * 
     */

    public function get($where, $tabela, $fields = "*") {

        $data = $this->db->table($tabela)
                ->select($fields)
                ->where($where)
                ->whereNull('deleted_at')
                ->get();

        return Array('status' => 'sucess', 'data' => $data);
    }

}
