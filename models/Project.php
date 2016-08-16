<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/07/12
 * Time: 21:47
 *
 * projectクラス
 */
namespace models;

class Project extends BaseModel{

    /**
     * $user_idを受けとてって、全てのプロジェクトデータを返す
     * なければから配列が返る
     *
     * @param $user_id
     * @return array
     */
    static public function getAll($user_id){
        $sql = "SELECT * FROM project WHERE user_id = :user_id ORDER BY view_order ";

        new Project();

        $ret_array = [];
        foreach(self::$pdo->fetchAll($sql, ['user_id' => $user_id]) as $record){
            $ret_array[] = self::getProjectDataFromRecord($record);
        }
        return $ret_array;
    }

    static public function getProject($id){
        $sql = "SELECT * FROM project WHERE id = :id ";

        new Project();

        return self::$pdo->fetch($sql, ['id' => $id]);
    }

    /**
     * プロジェクトの新規登録。
     * view_orderは既存の最大値+100に設定される
     *
     * @param $name プロジェクト名。文字列。空文字NG
     * @param $user_id ユーザーid
     * @return array
     */
    static public function newProject($name, $user_id){
        $ret_array = [
            'project_data' => [],   //新規登録したprojectデータをいれる
            'error_message' => [],  //引数名をキーとしてエラーメッセージをいれる
        ];

        //バリデーション
        $error_message = [];
        if(!(is_string($name) && $name != "")){
            $error_message['name'] = "プロジェクト名を入力してください";
        }
        $max_view_order_sql = "SELECT MAX(view_order) as max_view_order FROM project WHERE user_id = :user_id GROUP BY user_id ";
        new Project();
        $result = self::$pdo->fetch($max_view_order_sql, [':user_id' => $user_id]);
        if(!$result){
            $error_message['user_id'] = "user_idが存在しません";
        }
        if(count($error_message) > 0){
            $ret_array['error_message'] = $error_message;
            return $ret_array;
        }

        //新規登録処理
        //トランザクションスタート
        self::$pdo->beginTransaction();

        //プロジェクト新規登録
        $max_view_order = $result['max_view_order'];
        $view_order = $max_view_order + 100;
        $add_sql = "INSERT INTO project (name, view_order, user_id, root_todo_id, created) VALUE (:name, :view_order, :user_id, :root_todo_id, :created) ";
        self::$pdo->execute($add_sql, [
            ':name' => $name,
            ':view_order' => $view_order,
            ':user_id' => $user_id,
            ':root_todo_id' => 0,//すぐあとで登録するproject_todo_idが後ではいる
            ':created' => date("Y-m-d H:i:s"),
        ]);
        //プロジェクト登録idを取得
        $insert_id = self::$pdo->lastInsertId('id');

        //project_rootになるTodoを作成し、そのidを習得
        $root_todo_id = Todo::newRootTodo($insert_id, $user_id);

        //root_todo_idをプロジェクトに登録
        $update_project_sql = "UPDATE project SET root_todo_id = :root_todo_id WHERE id = :id ";
        self::$pdo->execute($update_project_sql, [
            ':root_todo_id' => $root_todo_id,
            ':id' => $insert_id,
        ]);

        //コミット
        self::$pdo->commit();
        $get_insert_data_sql = "SELECT * FROM project WHERE id = :id ";
        $ret_array['project_data'] = self::$pdo->fetch($get_insert_data_sql, [':id' => $insert_id]);
        return $ret_array;
    }

    /**
     * sqlから取得したprojestデータの数値をint型に変換して返す
     *
     * @param $record
     * @return array
     */
    static private function getProjectDataFromRecord($record){
        $ret_array = [
            'id'            => (int) $record['id'],
            'name'          => $record['name'],
            'view_order'    => $record['view_order'],
            'user_id'       => $record['user_id'],
            'created'       => $record['created'],
            'modified'      => $record['modified'],
        ];
        return $ret_array;
    }
}