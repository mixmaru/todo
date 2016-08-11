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

    //遷移url
    private $url = [
        'do_check' => "?controller=TodoList&action=Check",
        'project_list' => "/?controller=TodoList&action=List",
        'daily_list' => "/?controller=TodoList&action=DayList",
        'todo_edit' => "/?controller=TodoList&action=Edit",
    ];
    public function __construct()
    {
        $this->request = new Request();
        $this->flash = new FlashMessage();
        $this->renderer = new View();
    }

    /**
     * 日毎Todo一覧表示
     */
    public function actionDayList(){
        if($this->request->getMethod() !== "get"){
            //404を表示する
            $this->renderer->renderError(404);
        }

        //指定日のtodoデータを取得する
        //todo:user_idをログインユーザーのものにする
        //todo:日付を当日のものにする
        $todo_data_list = Todo::getTodoListByDay(1, "2016-07-20", "2016-07-21");

        //表示する
        $this->renderer->render("day_list", [
            'page_title' => "todoリスト",
            'todo_data_list' => $todo_data_list,
            'url' => $this->url,
            'current' => $this->url['daily_list'],
        ]);
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
            'url' => $this->url,
            'current' => $this->url['project_list'],
        ]);
    }

    /**
     * todoデータの変更
     */
    public function actionEdit(){
        $method = $this->request->getMethod();
        if($method === "get"){
            //編集入力フォーム
            //バリデーション（入力を想定しているのはint id(必須ではない)）
            $input_id = $this->request->get("id");
            if($input_id){
                if(!is_numeric($input_id)){
                    //不正な値の場合は値を捨てる
                    $input_id = false;
                }
            }
            $todo_data = [];
            if($input_id){
                //データ取得
                $todo_data = Todo::getTodo($input_id, 1);//todo: ログイン機能つけるまでuser_idは1に決め打ち
            }
            //すべてのプロジェクトデータを取得
            $all_project = [];
//            $all_project_data = Project::getAll(1);
            //Todoデータがあれば全てのTodoリストデータを取得
            $all_todo_list = [];
            if(!empty($todo_data)){
                $all_todo_list = Todo::getTodoListByUser(1, $todo_data['project_id']);
            }
            $this->renderer->render("todo_modify", [
                'todo_data' => $todo_data,
                'all_project' => $all_project,
                'all_todo_list' => $all_todo_list,
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
    }

    /**
     * todoデータのチェックとアンチェックを行う。
     * postのみ受け付ける
     */
    public function actionCheck(){
        $method = $this->request->getMethod();
        if($method !== "post"){
            //404表示
            $this->renderer->renderError(404);
        }

        //データ取得
        $input_data = $this->request->post();

        //バリデーション。入力を想定しているのは、int todo_id(必須), change_to(必須): "done" or "undone", redirect_url(必須)
        $valid_error_msg = [];
        if(  empty($input_data['todo_id'])
          || !is_numeric($input_data['todo_id'])
          || empty($input_data['change_to'])
          || !in_array($input_data['change_to'], ["done", "undone"])
          || empty($input_data['redirect_url'])){
            $this->renderer->renderError(400);
        }

        $change_to = $input_data['change_to'];
        $todo_id = $input_data['todo_id'];
        $redirect_url = $input_data['redirect_url'];

        //指定idのtodoデータのdoneステータスを指定のものに更新する
        try{
            switch($change_to){
                case "done":
                    Todo::finishTodo($todo_id);
                    break;
                case "undone":
                    Todo::unFinishTodo($todo_id);
                    break;
                default:
                    $this->renderer->renderError();
                    break;
            }
        }catch (\Exception $e){
            //データ更新失敗
            //todo: エラメッセージをセットして一覧ページへリダイレクト。エラーメッセージを表示する
            echo "データ更新失敗";
        }

        //一覧ページへリダイレクト。todo:リクエストクラスで行うようにする？
        header('Location: '.$redirect_url);
        exit();
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