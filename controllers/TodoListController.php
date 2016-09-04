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
use forms\TodoEditForm;
use forms\ParentTodoEditForm;
use models\Todo;
use models\Project;
use classes\View;
use services\TodoService;
use services\ProjectService;

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
        $todo_data_list = TodoService::getTodoListByDay(1, "2016-07-20", "2016-07-21");

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
        $todo_data_list = TodoService::getTodoListByUser(1);

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
        $form = new TodoEditForm();

        if($method === "post"){
            //入力値の取得
            $form->loadArray($this->request->post());
            if($form->validate(TodoEditForm::VALIDATION_PRE_CHECK)){
                $form->temporarySave();
                //親todo指定ページへリダイレクト
                header('Location: /?controller=TodoList&action=EditParent');
                return;
            }
            //エラー
            $error_message = $form->error_messages;
        }

        //編集対象Todoデータを取得。データがなければ新規作成
        $target_todo = [];
        $input_id = $this->request->get("id");
        if($input_id){
            //todoデータ取得
            $target_todo = TodoService::getTodoById($input_id, 1);//todo: ログイン機能をつけるまでuser_idは1に決め打ち
        }
        $all_project = ProjectService::getAllProjectByUserId(1);

        //編集入力フォーム表示。
        $this->renderer->render("todo_modify", [
            'page_title' => "Todo編集",
            'todo_data' => $target_todo,
            'all_project' => $all_project,
            'all_todo_list' => [],
            'url' => $this->url,
            'error_message' => $error_message,
            'input_data' => $form->getArray(),
        ]);
    }

    public function actionEditParent(){
        $form = new TodoEditForm();
        if(!$form->temporaryLoad()){
            //前ページでの入力データがない。最初のページへリダイレクト
            header('Location: /?controller=TodoList&action=Edit');
            return;
        }
        $error_message = [];
        //プロジェクトが新規登録だった場合は親Todoを指定する必要がないので、登録処理に入る.
        $method = $this->request->getMethod();
        if($form->project_id == -1 || $method == "post"){
            //親Todo入力値の取得
            if($method == "post") $form->parent_todo_id = $this->request->post("parent_todo_id");
            if($form->validate(TodoEditForm::VALIDATION_LAST_CHECK)){//念のためバリデーション
                var_dump("登録処理");
                //Todoとプロジェクトの登録処理。
                //完了画面へリダイレクト
                $form->temporaryDestroy();
                return;
            }elseif(false){
                //$parent_todo_id以外のエラーがある場合は不正な操作としてエラー
                $form->temporaryDestroy();
                $this->renderer->renderError(400);
                return;
            }
        }

        //親todoの選択画面を表示
        //プロジェクトidから全てのtodoデータを取得する。
        $todos = TodoService::getTodoListByProjectId($form->project_id);
        //親Todoのidを取得する
        $target_parent_todo_id = ($form->todo_id == -1) ? false : TodoService::getParentTodoIdById($form->todo_id);

        $this->renderer->render("parent_todo_modify", [
            'page_title' => "親Todo編集",
            'target_parent_todo_id' => $target_parent_todo_id,
            'todos' => $todos,
            'error_message' => $error_message,
            'input_data' => $form->getArray(),
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