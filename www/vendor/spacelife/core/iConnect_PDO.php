<?php
namespace spacelife\core;

use \PDO;

/**
* iConnect
*    This new iConnect version uses PDO to connect to mysql ...
*/
class iConnect_PDO
{

    protected $classname = 'iConnect_PDO';

    //  overloading config to prevent debug display
    protected $config = [
        'server'   => 'localhost',
        'user'     => false,
        'password' => false,
        'database' => false
        ];

    protected $cnx = false;

    /*
    *   __construct
    *
    */
    public function __construct($config)
    {
        $this->config['server'] = $config->database->server;
        $this->config['user'] = $config->database->user;
        $this->config['password'] = $config->database->password;
        $this->config['database'] = $config->database->database;

        try {
            $cnx = new PDO('mysql:host='.$this->config['server'].';dbname='.$this->config['database']
                , $this->config['user']
                , $this->config['password']
                , array(
                    PDO::ATTR_PERSISTENT => true
                    , PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    )
                );

            $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $cnx->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            $this->cnx = $cnx;

        } catch (PDOException $e) {
            if ($config->debug_trace === true) {
                die($e->getMessage());
            } else {
                die('Could not connect to database');
            }
        }


    }


    /**
    *   connect
    *
    **/
    public function connect()
    {
        return $this->cnx;
    }

    /**
    *   execute
    *
    **/
    public function execute($sql, $values)
    {
        $stmt = $this->cnx->prepare($sql);

        return $stmt->execute($values);
    }


    /*
    *   query
    *       description
    */
    public function query($sql)
    {
        return $this->cnx->query($sql);
    }

    /*
    *   select
    *       select data
    */
    public function select($sql, $values = [], $fetch_class_name = false)
    {
        $pre = $this->cnx->prepare($sql);
        $pre->execute($values);
        if ($fetch_class_name) {
            $pre->setFetchMode(PDO::FETCH_CLASS, $fetch_class_name);
        } else {
            $pre->setFetchMode(PDO::FETCH_OBJ);
        }
        return $pre->fetchAll();
    }

    /*
    *   selectOne
    *       select first data
    */
    public function selectOne($sql, $values = [], $fetch_class_name = false)
    {
        $pre = $this->cnx->prepare($sql);
        $pre->execute($values);
        if ($fetch_class_name) {
            $pre->setFetchMode(PDO::FETCH_CLASS, $fetch_class_name);
        } else {
            $pre->setFetchMode(PDO::FETCH_OBJ);
        }
        return $pre->fetch();
    }

    /**
    *   lastInsertId
    *
    **/
    public function lastInsertId()
    {
        return $this->cnx->lastInsertId();
    }


    /*
    *   createEvent
    *       creates a mysql event
    */
    public function createEvent($event)
    {
        //  check event data
        if (is_array($event)) {
            //  name
            if (isset($event['name']) && is_string($event['name'])) {
                $name = $event['name'];
            } else {
                // generate a psuedo random event name
                $name = md5(microtime(true) + rand(1000));
            }
            //  body
            if (isset($event['body']) && is_string($event['body'])) {
                $body = $event['body'];
            } else {
                $this->error('invalid event body');
            }
            //  schedule
            if (isset($event['schedule'])) {
                $schedule = $event['schedule'];
            } else {
                $this->error('invalid event schedule');
            }

        } elseif (is_object($event)) {
            if (isset($event->name) && is_string($event->name)) {
                $name = $event->name;
            } else {
                $name = md5(microtime(true) + rand(1000));
            }
            if (isset($event->body) && is_string($event->body)) {
                $body = $event->body;
            } else {
                $this->error('invalid event body');
            }
            if (isset($event->schedule) && is_string($event->schedule)) {
                $schedule = $event->schedule;
            } else {
                $this->error('invalid event schedule');
            }
        } else {
            $this->error('invalid event format');
        }

        //  checking recurring or not and building schedule accordingly
        if (is_string($schedule)) {
            $schedule = " at $schedule ";
        } elseif (is_array($schedule)) {
            if (isset($schedule['start'])) {
                $t_start =  $schedule['start'];
            } else {
                $this->error('missing start schedule');
            }
            if (isset($schedule['end'])) {
                $t_end = $schedule['end'];
            } else {
                $this->error('missing end schedule');
            }
            if (isset($schedule['every'])) {
                $t_every = $schedule['every'];
            } else {
                $this->error('missing recurring time');
            }
            $schedule = " every $t_every starts $t_start ends $t_end ";
        } else {
            $this->error('invalid schedule');
        }

        $sql = "create event $name ";
        $sql .= " on schedule $schedule ";
        $sql .= " on completion not preserve ";
        $sql .= " do BEGIN ";
        $sql .= " $body ";
        $sql .= " END";

        $this->query($sql);

    }

    /*
    *   __toString
    *       returns classname
    */
    public function __toString()
    {
        return $this->classname;
    }

    /**
    *   error
    *
    **/
    protected function error($message)
    {
        die($message);
    }

}
