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
use classes\View;

class TodoListController
{
    private $request;
    private $renderer;  //レンダラー。テンプレートエンジンオブジェクトを格納する
    public function __construct()
    {
        $this->request = new Request();
        $this->flash = new FlashMessage();
        $this->renderer = new View();
    }

    /**
     * todo一覧表示
     */
    public function actionList(){
        if($this->request->getMethod() !== "get"){
            //404を表示する
            $this->renderer->renderError(404);
            exit();
        }

        //全てのtodoデータを取得する
        $todo_data_list = Todo::getTodoListByUser(1);

        //表示する
        $this->renderer->render("list", [
            'page_title' => "todoリスト",
            'todo_data_list' => $todo_data_list,
        ]);
    }

    /**
     * todoデータの変更
     */
    public function actionEdit(){
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
                $message = "データの変更に失敗しました";
                //データ変更に失敗
                $this->flash->set($message);
                //ログへ記録
                error_log($message);
            }
            //一覧へリダイレクト
            header('Location: /');
            exit();
        }else{
            //404
        }
        */
    }

    /**
     * todoデータのチェックとアンチェックを行う。
     * postのみ受け付ける
     */
    public function actionCheck(){
        $method = $this->request->getMethod();
        if($method !== "post"){
            //404表示
        }

        //データ取得
        $input_data = $this->request->post();

        //バリデーション。入力を想定しているのは、int id(必須), 0 or 1 check(必須)
        /*todo
        if(バリデーションng){
            //不正な値エラー
        }

        //指定idのtodoデータのdoneステータスを指定のものに更新する
        if(指定idのtodoデータが存在しない){
            //不正な値エラー
        }
        $todo_obj = new Todo($id);
        try{
            if($input_data['check']){
                $todo_obj->setDone();
            }else{
                $todo_obj->setUnDone();
            }
        }catch(Exeption $e){
            $message = "データの更新に失敗しました";
            $this->flash->set($message);
            //ログへ記録
            error_log($message);
            if(リファラがある){
                //リファラへリダイレクト
                header('Location: リファラ');
            }//リファラがない場合は一覧へ。
        }
        //一覧へリダイレクト
        header('Location: /');
        exit();
        */
    }

    /**
     * todoデータの削除
     * postのみ受け付ける
     */
    public function actionDelete(){
        $method = $this->request->getMethod();
        if($method !== "post"){
            //404表示
        }

        //データ取得
        $input_data = $this->request->post();

        //バリデーション。入力を想定しているのは、int id(必須)
        /*todo
        if(バリデーションng){
            //不正な値エラー
        }

        $todo_obj = new Todo($data['id']);
        if($todo->id !== null){
            $todo->delete();
        }

        $this->flash->set("削除しました。");

        //一覧へリダイレクト
        header('Location: /');
        exit();
        */
    }

    /**
     * テスト用メソッド
     */
    public function actionTest(){
        $this->renderer->renderError(404);
    }
}