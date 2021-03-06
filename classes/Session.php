<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/08/26
 * Time: 22:11
 */

namespace classes;


class Session
{
    private $singleton;

    public function __construct()
    {
        if(session_status() == PHP_SESSION_NONE){
            session_start();//todo:セッション周り調べる
        }
    }
    public function set($key, $value){
        $_SESSION[$key] = $value;
    }
    public function get($key){
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    public function delete($key){
        unset($_SESSION[$key]);
    }
}