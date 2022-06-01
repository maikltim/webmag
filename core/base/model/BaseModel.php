<?php 


namespace core\base\model;


use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel extends BaseModelMethod
{

    use Singleton;

    protected $db;

    private function __construct()
    {
        $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);

        if($this->db->connect_error) {
            throw new DbException('Ошибка подключения к базе данных: ' . $this->db->connect_errno . ' ' . $this->db->connect_error);
        }

        $this->db->query("SET NAMES UTF8");
    }

    final public function query($query, $crud = 'r', $return_id = false) {
        $result = $this->db->query($query);

        if($this->db->affected_rows === -1) {
            throw new DbException('error in SQl request: ' . $query . ' - '. $this->db->errno . ' ' . $this->db->error);
        }

        switch($crud){
            case 'r':
                if($result->num_rows) {
                    $res = [];

                    for($i = 0; $i < $result->num_rows; $i++) {
                        $res[] = $result->fetch_assoc();
                    }
                    return $res;
                }

                return false;

                break;
            case 'c':
                if($return_id) return $this->db->insert_id;

                    return true;

                    break;

            default:
                return true;
                break;

        }
    }

    final public function get($table, $set = []) {
        $fields = $this->createFields($set, $table);
        $where = $this->createWhere($set, $table);
        $order = $this->createOrder($set, $table);

        if(!$where) $new_where = true;
            else $new_where = false;
        $join_arr = $this->createJoin($set, $table, $new_where);

        $fields .= $join_arr['fields'];
        $join = $join_arr['join'];
        $where .= $join_arr['where'];

        $fields = rtrim($fields, ',');

        

        $limit = $set['limit'] ? $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);


    }


    final public function add($table, $set) {

        $set['fields'] = (is_array($set['fields']) && !empty($set['fields'])) ? $set['fields'] : false;
        $set['files'] = (is_array($set['files']) && !empty($set['files'])) ? $set['files'] : false;
        $set['return_id'] = $set['return_id'] ? true : false;
        $set['except'] = (is_array($set['except']) && !empty($set['except'])) ? $set['except'] : false;

        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);

        if($insert_arr) {
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES ({$insert_arr['values']})";
            return $this->query($query, 'c', $set['return_id']);
        }

        return false;

    }

}