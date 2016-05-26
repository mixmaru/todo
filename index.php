<?php
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
 */

use controllers;

define("ROOT_PATH", dirname(__FILE__)."/");
/*todo:
//値の読み込み
$controller_name = $_GET['controller'];
$action_name = $_GET['action'];

//対象コントローラーとアクションが存在するか確認
if(controllerが存在しない または　actionが存在しない){
    404の表示
}

//実行
$controller = new $controller_name();
$controller->"action".$action_name();
*/
