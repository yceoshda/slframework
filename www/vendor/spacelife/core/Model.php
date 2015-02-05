<?php
namespace spacelife\core;

use spacelife\exception\SLException;
use spacelife\tools\Collection;

/**
* Model
*/
class Model
{
    //  db connection
    protected $db = false;

    //  config
    protected $config = false;

    //  single table basics
    protected $table = false;
    protected $primaryKey = 'id';

    //  entity namespace
    protected $entityNS = false;
    protected $defaultEntity = false;

    //  data validation
    protected $validation_rules = array();
    public $validation_errors = false;

    //  data validation auto rules
    protected $validation = false;

    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;

        if ($this->validation && is_array($this->validation)) {
            foreach ($this->validation as $name => $rules) {
                $this->addRule($name, $rules);
            }
        }
    }

    /*
    *   save
    *       saves data to a table
    */
    public function save($data)
    {

        $sql = '';
        $pk = $this->primaryKey;

        //  building fields
        $fields = [];
        foreach ($data as $key => $value) {
            if ($key != $pk) {
                if ($key == 'updated') {
                    // $value = 'CURRENT_TIMESTAMP';
                    $value = date('Y-m-d H:i:s');
                }
                $fields[] = " $key=:$key ";
                $values[":$key"] = $value;
            }
        }

        //  final fields with commas
        $fieldset = implode(', ', $fields);

        //  differentiate insert / update based on id presence
        if (isset($data->id) && $data->id != 0 && $data->id != '') {
            $values[":$pk"] = $data->id;
            $sql = "update {$this->table} set $fieldset where $pk=:$pk";
        } else {
            $sql = "insert into {$this->table} set $fieldset";
        }

        $this->db->execute($sql, $values);

        if (isset($data->id) && $data->id != 0 && $data->id != '') {
            return $data->id;
        } else {
            return $this->db->lastInsertId();
        }

    }

    /*
    *   find
    *       retrieve data
    */
    public function find($params = false)
    {

        $search = '';
        $values = [];
        $fields = ' * ';
        $limit = '';
        $order = '';
        $table = ' '.$this->table.' ';

        //  if there are parameters
        if ($params && is_array($params)) {

            //  filter (where)
            if (isset($params['filter'])) {
                foreach ($params['filter'] as $key => $value) {
                    $searchlist[] = $key.'=:'.$key;
                    $values[":$key"] = $value;
                }

                $search = " where ".implode(' and ', $searchlist);
            }

            //  fields
            if (isset($params['fields'])) {
                $fields = ' '.implode(', ', $params['fields']).' ';
            }

            //  limit
            if (isset($params['limit'])) {
                if (is_array($params['limit'])) {
                    $limit = ' limit '.$params['limit'][0].', '.$params['limit'][1].' ';
                } else {
                    $limit = ' limit '.$params['limit'].' ';
                }
            }

            //  order
            if (isset($params['order'])) {
                if (is_array($params['order'])) {
                    $order = ' order by '.implode(', ', $params['order']).' ';
                } else {
                    $order = ' order by '.$params['order'].' ';
                }
            }

            //  from
            if (isset($params['table'])) {
                if (is_array($params['table'])) {
                    $table = ' '.implode(', ', $params['table']).' ';
                } else {
                    $table = ' '.$params['table'].' ';
                }

            }

        } else {
            return false;
        }

        $sql = "select $fields from $table $search $order $limit";

        if (array_key_exists('entity', $params) && $params['entity'] !== false) {
            $fetch_class = $this->assignEntity($params['entity']);
        } else {
            $fetch_class = false;
        }

        if (array_key_exists('one', $params) && $params['one'] == true) {
            return $this->db->selectOne($sql, $values, $fetch_class);
        }

        return $this->db->select($sql, $values, $fetch_class);

    }

    /*
    *   findFirst
    *       retrieve first
    */
    public function findFirst($params)
    {
        $params['one'] = true;
        return $this->find($params);
    }

    /*
    *   delete
    *       deletes data based on a filter
    */
    public function delete($filter)
    {
        if (!is_array($filter)) {
            return false;
        }

        foreach ($filter as $key => $value) {
            $searchlist[] = $key.'=:'.$key;
            $values[":$key"] = $value;
        }
        $search = " where ".implode(' and ', $searchlist);

        $sql = "delete from ".$this->table." ".$search;

        return $this->db->execute($sql, $values);
    }

    /**
    *   query
    *       sends a query directly to the database (using prepared statements if values are separated)
    **/
    public function query($sql, $values = false)
    {
        if ($values !== false) {
            return $this->db->execute($sql, $values);
        } else {
            return $this->db->query($sql);
        }

    }

    /*
    *   validate
    *       data validation
    */
    public function validate($data, $rules_to_apply)
    {
        if (!is_object($data)) {
            // $data = new Collection($data);
            throw new SLException("Error validating data MUST be an object", 1);
        }

        $error = array();
        $flag = true;

        foreach ($rules_to_apply as $name) {
            //  match rule
            if (isset($this->validation_rules[$name])) {
                $rule = $this->validation_rules[$name];
            } else {
                throw new SLException("Error validating $name rule does not exist", 500);
            }

            //  if required
            if ((!isset($data->$name) || $data->$name === false) && (isset($rule['required']) && $rule['required'] === true)) {
                $error[$name] = 'required';
                $flag = false;
            }

            //  if var is present
            if (isset($data->$name)) {
                //  filter unwanted characters
                if ($rule['filter']) {
                    if (preg_match("/[".$rule['filter']."]/", $data->$name)){
                        $error[$name] = 'filter';
                        if ($rule['filter_clean']) {
                            $data->$name = preg_replace('/['.$rule['filter'].']/', '', $data->$name);
                        } else {
                            $flag = false;
                        }
                    }
                }
                //  after cleaning: check length
                if (is_array($rule['required'])) {
                    //  too short
                    if (strlen($data->$name) < $rule['required'][0]) {
                        $error[$name] = 'required';
                        $flag = false;
                    }
                    //  too long
                    if (strlen($data->$name) > $rule['required'][1]) {
                        $error[$name] = 'required';
                        $flag = false;
                    }
                } else {
                    //  no upper limit
                    if (strlen($data->$name) < $rule['required']) {
                        $error[$name] = 'required';
                        $flag = false;
                    }
                }
                //  pattern
                if ($rule['pattern']) {
                    if (!preg_match('/'.$rule['pattern'].'/', $data->$name)) {
                        $error[$name] = 'pattern';
                        $flag = false;
                    }
                }
                //  extra
                if ($rule['extra']) {
                    if (!$this->$rule['extra']($data)) {
                        $error[$name] = 'extra';
                        $flag = false;
                    }
                }
            //  end if var is present
            }
        // end foreach
        }

        $this->validation_errors = $error;

        //  if there was errors: return false
        if ($flag === false) {
            return false;
        }
        //  else return data
        return $data;
    }

    /*
    *   addRule
    *       adds a rule for data validation
    */
    public function addRule($name, $rules = array())
    {
        $this->validation_rules[$name] = $this->createRule($name, $rules);
    }

    /*
    *   createRule
    *       adds a rule for data validation
    */
    public function createRule($name, $rules = array())
    {
        if (!isset($rules['required'])) {
            $rules['required'] = 0;
        }
        if (!isset($rules['filter'])) {
            $rules['filter'] = false;
        }
        if (!isset($rules['filter_clean'])) {
            $rules['filter_clean'] = false;
        }
        if (!isset($rules['pattern'])) {
            $rules['pattern'] = false;
        }
        if (!isset($rules['extra'])) {
            $rules['extra'] = false;
        }
        if (!isset($rules['error'])) {
            $rules['error'] = false;
        }
        return $rules;
    }

    /**
     * build an entity name based on $this->entityNS
     * @param  string $entity entity name
     * @return string         entity fqdn
     */
    public function assignEntity($entity)
    {
        //  if entity is just 'true' try the default entity else return false to deactivate entity support
        if ($entity === true) {
            if ($this->defaultEntity !== false) {
                return $this->assignEntity($this->defaultEntity);
            } else {
                return false;
            }
        }

        //  if entity name has no \ then add the default NS if it exists
        if (!strpos($entity, '\\')) {
            if ($this->entityNS !== false) {
                $entity_fqdn = $this->entityNS.'\\'.$entity;
            } else {
                return false;
            }
        } else {
            $entity_fqdn = $entity;
        }

        return $entity_fqdn;
    }

}