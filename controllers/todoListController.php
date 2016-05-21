<?php

/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/21
 * Time: 16:30
 * todoリストの一覧表示、編集用コントローラー
 */
namespace controllers;

use classes\FlashMessage;
use classes\Request;
use models\Todo;
use models\TodoList;
use classes\Response;

class TodoListController
{
    private $request;
    public function __construct()
    {
        $this->request = new Request();
        $this->responce = new Response();
        $this->flash = new FlashMessage();
    }

    /**
     * todo一覧表示
     */
    public function actionList(){
        /*todo:

        if($this->request->getMethod() !== "get"){
            //404を表示
        }

        //全てのtodoデータを取得する
        $todo_list_obj = new TodoList();
        $todo_data_list = $todo_list_obj->getDataArray();

        //表示する
        $this->render("", [
            'todo_data_list' => $todo_data_list,
        ]);
        */
    }

    /**
     * todoデータの変更
     */
    public function actionUpdate(){
        //todo: チェックonもこのアクションで行うか検討

        /*todo:
        $method = $request->getMethod();
        if($method === "get"){
            //編集入力フォーム
            //バリデーション（入力を想定しているのはint id(必須ではない)）
            if(バリデーションng){
                //不正な値エラー表示
            }
            $id = isset($data['id']) ? $data['id'] : null;
            $todo_obj = new Todo($id);
            $todo_data = $todo_obj->getPropertyToArray();
            $this->render("", [
                'todo_data' => $todo_data,
            ]);
        }elseif($method === "post"){
            //編集実行
            $input_data = $this->request->post();
            //バリデーション（入力を想定しているのはint id(任意), title(必須), limit_date(任意)）
            $todo_obj = new Todo($id);
            $todo_obj->dataLoad($input_data);
            try{
                $todo_obj->save();
            }catch(Exeption $e){
                //データ変更に失敗
                $this->flash->set("データの変更に失敗しました");
                //ログへ記録
            }
            //一覧へリダイレクト
        }else{
            //404
        }
        */
    }

    /**
     * todoデータの削除
     */
    public function actionDelete(){

    }

    private function render($template, $args = []){
        $this->responce->render($template, $args);
    }
}