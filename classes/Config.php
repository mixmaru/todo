<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/29
 * Time: 14:32
 *
 * 各種設定管理クラス
 */

namespace classes;


class Config
{
    //configデータの
    private $config_data;

    public function __construct()
    {
        try{
            $this->config_data = require CONFIG_DIR_PATH."config.php";
        }catch(\Exception $e){
            throw new \Exception("config/config.php が読み込めませんでした");
        }
    }

    public function getConfig(){
        return $this->config_data;
    }
}