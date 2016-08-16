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
use models\Project;
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
        $error_message = [];
        $input_data = [];

        if($method === "post"){
            //入力値の取得
            $input_data = $this->request->post();

            if(isset($input_data['delete'])){
                //指定Todoの削除
                var_dump("削除");
            }elseif(isset($input_data['up'])){
                if($input_data['project_id'] == -1){
                    //新規プロジェクト作成
                    $result = Project::newProject($input_data['new_project_name'], 1);//todo:ログインシステム実装するまでuser_idを1に決め打ち
                    if(count($result['error_message']) > 0){
                        //新規プロジェクト作成失敗(バリデートエラー)
                        if(isset($result['error_message']['user_id'])){
                            $this->renderer->renderError(500);
                            return;
                        }
                        if(isset($result['error_message']['name'])) $error_message['new_project_name'] = $result['error_message']['name'];
                    }else{
                        //新規プロジェクト作成成功
                        $input_data['project_id'] = $result['project_data']['id'];
                        unset($input_data['new_project_name']);
                    }
                }
                if($input_data['todo_id'] == -1){
                    //todo: 引数は$parent_path でなくて、$parent_idのほうがいいかも
                    //新規追加
                    //                    Todo::newTodo($title, $do_date, $limit_date, $parent_path, $project_id, $user_id);
                    var_dump("新規追加");
                }else{
                    //内容編集
                    var_dump("編集");
                    $tmp_error_msg = Todo::modifyTodo($input_data['todo_id'],
                                     $input_data['todo_title'],
                                     $input_data['todo_do_date'],
                                     $input_data['todo_limit_date'],
                                     $input_data['project_id'],
                                     1);
                    if(isset($tmp_error_msg['id'])) $error_message['todo_id'] = $tmp_error_msg['id'];
                    if(isset($tmp_error_msg['title'])) $error_message['todo_title'] = $tmp_error_msg['title'];
                    if(isset($tmp_error_msg['do_date'])) $error_message['todo_do_date'] = $tmp_error_msg['do_date'];
                    if(isset($tmp_error_msg['limit_date'])) $error_message['todo_limit_date'] = $tmp_error_msg['limit_date'];
                    if($input_data['project_id'] != -1){
                        if(isset($tmp_error_msg['project_id'])) $error_message['project_id'] = $tmp_error_msg['project_id'];
                    }
                }

            }else{
                $this->renderer->renderError(400);
                exit();
            }
            //一覧へリダイレクト
            if(count($error_message) <= 0){
                //            header('Location: /?controller=TodoList&action=List');
                var_dump("リダイレクト");
                exit();
            }
        }

        //編集対象Todoデータを取得
        $todo_data = [];
        $input_id = $this->request->get("id");
        if($input_id){
            //todoデータ取得
            $todo_data = Todo::getModifyTodoData($input_id, 1);//todo: ログイン機能つけるまでuser_idは1に決め打ち
            if(count($todo_data['error_message']) > 0){
                if(isset($todo_data['error_message']['user_id'])){
                    //user_idがおかしい場合はエラー表示
                    $this->renderer->renderError(400);
                    exit();
                }
                if(isset($todo_data['error_message']['id'])){
                    //input_idがおかしい場合は無視
                    $todo_data = [];
                }
            }
        }
        //すべてのプロジェクトデータを取得
        $all_project = Project::getAll(1);

        //編集入力フォーム表示。
        $this->renderer->render("todo_modify", [
            'page_title' => "Todo編集",
            'todo_data' => $todo_data['target_todo'],
            'all_project' => $all_project,
            'all_todo_list' => $todo_data['same_project_all_todo'],
            'url' => $this->url,
            'error_message' => $error_message,
            'input_data' => $input_data,
        ]);
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