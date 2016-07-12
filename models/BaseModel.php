<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/12
 * Time: 21:49
 */
namespace models;

use classes\Config;

class BaseModel{
    static protected $pdo;

    protected $db;

    public function __construct()
    {
        //pdo接続はインスタンス間で使い回す
        if(is_null(self::$pdo)){
            $config = new Config();
            $config_params = $config->getConfig();
            $db_params = $config_params['db'];
            self::$pdo = new \PDO("mysql:host=".$db_params['host'].";dbname=".$db_params['db'], $db_params['user'], $db_params['password']);
        }
        $this->db = self::$pdo;
    }

}