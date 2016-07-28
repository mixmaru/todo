<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/12
 * Time: 21:49
 */
namespace models;

use classes\Config;
use classes\UserPdo;

class BaseModel{
    static protected $pdo;

    protected $db;

    protected $id;
    protected $created;
    protected $modified;

    public function __construct()
    {
        //pdo接続はインスタンス間で使い回す
        if(is_null(self::$pdo)){
            $config = new Config();
            $config_params = $config->getConfig();
            $db_params = $config_params['db'];
            self::$pdo = new UserPdo("mysql:host=".$db_params['host'].";dbname=".$db_params['db'], $db_params['user'], $db_params['password']);
        }
        $this->db = self::$pdo;
    }

    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }

    public function getCreated(){
        return $this->created;
    }

    public function getModified(){
        return $this->modified;
    }

}