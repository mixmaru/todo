<?php
use classes\Config;

//var_dumpが省略しないように。。
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);
/**
* Created by IntelliJ IDEA.
* User: mix
* Date: 2016/05/21
* Time: 16:26
*
* エントリーポイント。
* 以下処理を行う。
* ・初期ロード
* ・urlを分析して対応するコントローラーのアクションを起動
* ※とりあえず、今はget値で実行するコントローラーとアクションを指定する。
* get値：controller コントローラー名
* get値：action アクション名
* 例：/?controller=hoge&action=huga
*　　　上記はhogeController->actionhugaが実行される
*/

//パス類
define("ROOT_PATH", dirname(__FILE__)."/../");
define("CONTROLLER_DIR_PATH", ROOT_PATH."controllers/");
define("MODEL_DIR_PATH", ROOT_PATH."models/");
define("VIEW_DIR_PATH", ROOT_PATH."views/");
define("TEMPLATE_DIR_PATH", VIEW_DIR_PATH."templates/");
define("VIEW_CACHE_DIR_PATH", VIEW_DIR_PATH."cache/");
define("CONFIG_DIR_PATH", ROOT_PATH."config/");

//オートロードの設定
spl_autoload_register(function($name){
    $name = str_replace("\\", DIRECTORY_SEPARATOR, $name);
    $file = ROOT_PATH.$name.".php";
    if(file_exists($file)){
        include_once $file;
    }else{
        throw new Exception("クラスファイルが存在しません");
    }
});

//composer用のオートロード
require_once ROOT_PATH."vendor/autoload.php";

//DB
$config = new Config();
$config_params = $config->getConfig();
switch(ENV){
    case "test":
        $db_params = $config_params['db']['test'];
        break;
    case "unit_test":
        $db_params = $config_params['db']['unit_test'];
        break;
}
define("DB_HOST", $db_params['host']);
define("DB_DB", $db_params['db']);
define("DB_USER", $db_params['user']);
define("DB_PASS", $db_params['password']);

