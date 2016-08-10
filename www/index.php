<?php
use classes\View;

define("ENV", "test");
include "../init/init.php";

//値の読み込み
$controller_name = $_GET['controller']."Controller";
$action_name = "action".$_GET['action'];

//指定されたコントローラーの存在確認
try{
    $controller_name = 'controllers\\'.$controller_name;
    $controller = new $controller_name();
}catch(Exception $e){
    //指定コントローラーが存在しない。404エラー
    $view = new View();
    $view->renderError(404);
    exit();
}


if(method_exists($controller, $action_name)){
    $controller->$action_name();
}else{
    //指定アクションが存在しない。404エラー
    $view = new View();
    $view->renderError(404);
    exit();
}

