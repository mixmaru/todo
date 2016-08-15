<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/21
 * Time: 16:40
 *
 * todoデータを表すモデルクラス
 */

namespace models;

use models\Project;

class Todo extends BaseModel
{
    /**
     * todoのidに対応する編集画面用Todoデータを返す。
     * 他のuserのTodoデータを取得しないように$user_idが必須
     *
     * @param $id
     * @param $user_id
     * @return array
     */
    public static function getModifyTodoData($id, $user_id){
        $ret_data = [
            'target_todo' => [],
            'same_project_all_todo' => [],
            'error_message' => [],
        ];

        //バリデーション
        if(!is_numeric($id)){
            $ret_data['error_message']['id'] = "idが数値ではない";
        }
        if(!is_numeric($user_id)){
            $ret_data['error_message']['user_id'] = "user_idが値ではない";
        }
        if(count($ret_data['error_message']) > 0){
            return $ret_data;
        }

        $result = self::getTodo($id, $user_id);
        if($result){
            //親idを取り出してデータとして持たせる
            $result['parent_id'] = self::convertParentIdByPath($result['path']);
            $ret_data['target_todo'] = self::getTodoDataFromRecord($result);

            //編集するTodoと同じプロジェクトに属するTodoデータを取得する
            $record = self::getProjectTodoRecords($user_id, null, null, $result['project_id']);
            if(count($record) > 0){
                $all_todo_list = self::makeProjectTodoListDataFromRecords($record, true);
                $ret_data['same_project_all_todo'] = $all_todo_list[0]['todo_data'];
            }
        }
        return $ret_data;
    }

    /**
     * 全てのプロジェクトデータと、それに関連する全てのTodoデータを取得
     *
     * 返却データの形
     * $ret_data = [
     *      [
     *          'project_data' => プロジェクトデータ,
     *          'todo_data' => [todotreeデータ],
     *      },
     *      [
     *          'project_data' => プロジェクトデータ,
     *          'todo_data' => [todotreeデータ],
     *      },
     * ];
     *
     * @param $user_id
     * @param null $project_id //対象プロジェクトを絞り込む
     * @return array
     */
    public static function getTodoListByUser($user_id, $project_id = null){
        $records = self::getProjectTodoRecords($user_id, null, null, $project_id);
        if(count($records) == 0){
            return [];
        }
        return self::makeProjectTodoListDataFromRecords($records, true);
    }

    /**
     * 日毎Todoリスト表示データの取得
     *
     * @param $user_id
     * @param $start_date :日にちを指定。$limitを指定しない場合はこの日のTodoリストが返る
     * @param null $limit_date :日にちを指定。$start_dateから$limit_dateの間で絞り込んだTodoリストが返る
     * @return array
     * @throws \Exception
     */
    public static function getTodoListByDay($user_id, $start_date, $limit_date = null){
        /**
         * 着手日か、締切日のどちらの日にちを使うかを判断する
         * todo_do_dateが指定範囲(getTodoListByDayの日にち指定)内なら、todo_do_dateを、そうでないなら$todo_limit_dateを返す
         *
         * @param $todo_do_date
         * @param $todo_limit_date
         * @return mixed
         */
        $resolveDate = function($todo_do_date, $todo_limit_date) use($start_date, $limit_date){
            $start_time = strtotime($start_date);
            $end_time = (isset($limit_date)) ? strtotime($limit_date) : $start_time;
            $todo_do_date_time = strtotime($todo_do_date);
            if($start_time <= $todo_do_date_time && $todo_do_date_time <= $end_time){
                return $todo_do_date;
            }else{
                return $todo_limit_date;
            }
        };
        $records = self::getProjectTodoRecords($user_id, $start_date, $limit_date);
        if(count($records) == 0){
            return [];
        }
        $ret_array = [];
        $tmp_date = $resolveDate($records[0]['do_date'], $records[0]['limit_date']);
        $tmp_records = [];
        foreach($records as $record){
            $date = $resolveDate($record['do_date'], $record['limit_date']);
            if($tmp_date != $date){
                //ためていたデータで配列を作り、ためていたデータを初期化
                $ret_array[] = [
                    'date' => $tmp_date,
                    'project_todo_data' => self::makeProjectTodoListDataFromRecords($tmp_records, false),
                ];
                $tmp_date = $resolveDate($record['do_date'], $record['limit_date']);
                $tmp_records = [];
            }
            $tmp_records[] = $record;
        }
        //バッファがあればそのデータ配列を作り、追加する
        if(count($tmp_records) > 0){
                $ret_array[] = [
                    'date' => $tmp_date,
                    'project_todo_data' => self::makeProjectTodoListDataFromRecords($tmp_records, false),
                ];
        }
        return $ret_array;
    }

    /**
     * 新しいTodoを保存する
     *
     * @param $title
     * @param $do_date
     * @param $limit_date
     * @param $parent_path
     * @param $project_id
     * @param $user_id
     * @return bool
     * @throws \Exception
     */
    public static function saveNewTodo($title, $do_date, $limit_date, $parent_path, $project_id, $user_id){
        //必須項目のチェック
        $error_msg = false;
        if(empty($title) || empty($parent_path) || empty($user_id)){
            $error_msg .= "title, parent_path, user_idは必須引数です\n";
        }
        //チェックが必要なものはチェックする
        //日付が正しいか確認
        foreach(['do_date', 'limit_date'] as $val_name){
            if(isset($$val_name)){
                if($$val_name !== date("Y-m-d", strtotime($$val_name))){
                    $error_msg .= "${val_name}の日付指定を正しく行ってください\n";
                }
            }
        }
        //parent_pathの指定が正しいかチェックしたいが、そのためにDBアクセスを行うのは負荷が高いので、チェックしない。
        //project_idとuser_idの存在整合性はDBで行っているのでここでは行わない
        if($error_msg){
            throw new \Exception($error_msg);
        }

        new Todo();
        //トランザクション開始
        if(!self::$pdo->beginTransaction()){
            throw new \Exception("トランザクション開始に失敗しました");
        }

        //todoテーブルに次に使われるid値を取得して登録するpathを用意
        $todo_table_data = self::$pdo->fetch("SHOW TABLE STATUS LIKE 'todo'");
        $next_id = (int) $todo_table_data['Auto_increment'];
        $path = $parent_path."${next_id}/";

        //保存用sqlを作成
        $sql = "INSERT INTO todo (title, do_date, limit_date, is_done, path, project_id, user_id, created) "
              ."VALUES (:title, :do_date, :limit_date, :is_done, :path, :project_id, :user_id, :created)";
        $params = [
            ':title' => $title,
            ':do_date' => $do_date,
            ':limit_date' => $limit_date,
            ':is_done' => "UNDONE",
            ':path' => $path,
            ':project_id' => $project_id,
            ':user_id' => $user_id,
            ':created' => date("Y-m-d H:i:s"),
        ];
        //sqlを実行
        self::$pdo->execute($sql, $params);
        //コミット
        self::$pdo->commit();
        return true;
    }

    /**
     * $idで指定したTodoのデータを上書き編集する
     * project_rootのTodoは編集できない
     *
     * @param $id
     * @param $title
     * @param $do_date
     * @param $limit_date
     * @param $project_id
     * @throws \Exception
     */
    static public function modifyTodo($id, $title, $do_date, $limit_date, $project_id, $user_id){
        $error_msg = [];
        //バリデーション
        $target_todo = self::getTodo($id, $user_id);
        if(empty($target_todo)){
            $error_msg['id'] = "todoのidが不正です";
        }
        //$titleが指定されているか？
        if(empty($title)){
            $error_msg['title'] = "titleを入力してください";
        }
        //$do_dateがセットされていれば日付か
        if(!empty($do_date) && $do_date !== date("Y-m-d", strtotime($do_date))){
            $error_msg['do_date'] = "正しく日付指定してください";
        }
        //$limit_dateがセットされていれば日付か
        if(!empty($limit_date) && $limit_date !== date("Y-m-d", strtotime($limit_date))){
            $error_msg['limit_date'] = "正しく日付指定してください";
        }
        //$project_idのプロジェクトは$user_idのプロジェクトであるか？
        $project_data = Project::getProject($project_id);
        if($project_data['user_id'] != $user_id){
            $error_msg['project_id'] = "プロジェクトを指定してください";
        }

        if(count($error_msg) > 0){
            return $error_msg;
        }

        /*
            //タイトルが入力されているか?
            if(empty($input_data['todo_title'])){
                $error_message['todo_title'] = "タイトルを入力してください";
            }
            //project_idに-1または存在するプロジェクトのidが指定されているか
            $allow_project_ids = array_merge([-1], array_column($all_project, "id"));
            if(!(isset($input_data['project_id']) && in_array($input_data['project_id'], $allow_project_ids))){
                $error_message['project_id'] = "プロジェクトを指定してください";
            }elseif($input_data['project_id'] == -1 && empty($input_data['new_project_name'])){
                //project_idが-1の場合、new_project_nameが入力されているか？
                $error_message['new_project_name'] = "新しいプロジェクト名を入力してください";
            }
            //parent_todo_idが-1または存在するTodoのidが指定されているか？
            $allow_todo_ids = [-1, 1,2,3];
            if(!(isset($input_data['parent_todo_id']) && in_array($input_data['parent_todo_id'], $all_todo_list))){
                $error_message['parent_todo_id'] = "親Todoを指定してください";
            }
            upかdeleteのどちらかが入力されているか？
            todo_idに-1以上の数値が入力されているか？
            todo_idが-1でない場合、そのtodo_idのTodoデータのuser_idはログインuser_idと同じか？

        */


        //入力値チェック
        $error_msg = false;
        //idの入力確認。タイトルの入力確認。$parent_pathの確認。
        if(empty($id) || empty($title) || empty($parent_path)){
            $error_msg .= "id, title, parent_pathは必須引数です\n";
        }
        //日付が正しいか確認
        foreach(['do_date', 'limit_date'] as $val_name){
            if(isset($$val_name)){
                if($$val_name !== date("Y-m-d", strtotime($$val_name))){
                    $error_msg .= "${val_name}の日付指定を正しく行ってください\n";
                }
            }
        }
        //入力が正しくなければエラー
        if($error_msg){
            throw new \Exception($error_msg);
        }

        new Todo();

        //project_rootは変更できないようにする
        $project_root_check_sql = "SELECT title FROM todo WHERE id = :id ";
        $result = self::$pdo->fetch($project_root_check_sql);
        if($result['title'] == "project_root"){
            throw new \Exception("project_rootは変更できません");
        }

        //内容保存処理
        $sql = "UPDATE todo SET title=:title, do_date=:do_date, limit_date=:limit_date, path=:path, project_id=:project_id "
              ."WHERE id = :id";
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':do_date' => $do_date,
            ':limit_date' => $limit_date,
            ':path' => $parent_path."${id}/",
            ':project_id' => $project_id,
        ];
        self::$pdo->execute($sql, $params);
        return true;
    }

    /**
     * 指定Todoを完了させる
     *
     * @param $todo_id
     */
    static public function finishTodo($todo_id){
        self::changeDone($todo_id, "DONE");
    }

    static public function unFinishTodo($todo_id){
        self::changeDone($todo_id, "UNDONE");
    }

    /**
     * 指定Todoを削除する
     *
     * @param $todo_id
     */
    static public function deleteTodo($todo_id){
        $sql = "DELETE FROM todo WHERE id = :id ";
        new Todo();
        self::$pdo->execute($sql, [':id' => $todo_id]);
    }

    /**
     * todo_idとuser_idから対応するtodoデータのレコードを返す
     *
     * @param $id
     * @param $user_id
     * @return array
     */
    static private function getTodo($id, $user_id){
        new Todo();
        $sql = "SELECT * FROM todo WHERE id = :id AND user_id = :user_id";
        return self::$pdo->fetch($sql, [':id' => $id, 'user_id' => $user_id]);
    }

    /**
     * todoのis_doneカラムを$toの値に変更する
     *
     * @param $todo_id
     * @param $to       :"DONE" or "UNDONE";
     */
    static private function changeDone($todo_id, $to){
        $sql = "UPDATE todo SET is_done=:is_done WHERE id = :id ";
        $params = [
            ':is_done' => $to,
            ':id' => $todo_id,
        ];
        new Todo();
        self::$pdo->execute($sql, $params);
    }

    /**
     * getProjectTodoRecords()の返り値の$recordsを受け取り、controllerに返すためのプロジェクトとTodoのデータリストに整形する
     * $tree=trueにすると、Todoデータをツリー構造にしようとするが、getProjectTodoRecordsで日毎のデータとして取得している場合はうまくTreeにならないと思う。
     * 基本は全てのデータが揃っている時は$tree=true,そうでない時は$tree=falseで使う
     *
     * @param $records
     * @param bool $tree
     * @return array
     */
    private static function makeProjectTodoListDataFromRecords($records, $tree = false){
        $ret_data = [];
        $count = 0;
        $tmp_project_id = $records[0]['project_id'];
        $tmp_records = [];
        foreach($records as $record){
            if($tmp_project_id != $record['project_id']){
                //１プロジェクト分のデータを作成して次の処理へ進む
                $ret_data[$count] = [
                    'project_data' => self::getProjactDataFromRecord($tmp_records[0]),
                    'todo_data' => ($tree) ? self::makeTreeData($tmp_records) : self::makeListTodoData($tmp_records),
                ];
                //次の処理のための処理
                $tmp_records = [];
                $tmp_project_id = $record['project_id'];
                $count++;
            }
            $tmp_records[] = $record;
        }
        if(count($tmp_records) > 0){
            $ret_data[$count] = [
                'project_data' => self::getProjactDataFromRecord($tmp_records[0]),
                'todo_data' => ($tree) ? self::makeTreeData($tmp_records) : self::makeListTodoData($tmp_records),
            ];
        }
        return $ret_data;
    }

    /**
     * @param $records       :同一プロジェクトの深さ順にならんだTodoレコード配列
     * @return array
     */
    private static function makeTreeData($records){
        $ret_tree_data = [];//返却用Tree構造データ
        $tmp_list_data = [];//Treeデータ作成に必要なlistデータ
        foreach($records as $todo_data){
            $path_array = array_filter(explode('/', $todo_data['path']), 'strlen');
            $current_id = array_pop($path_array);
            $parent_id = array_pop($path_array);
            $tmp_list_data[$current_id] = [
                'data' => self::getTodoDataFromRecord($todo_data),
                'child' => [],
//                'parent' => null,
            ];
            if(is_null($parent_id)){
                $ret_tree_data[$current_id] = &$tmp_list_data[$current_id];
            }else{
//                $tmp_list_data[$current_id]['parent'] = &$tmp_list_data[$parent_id];
                $tmp_list_data[$parent_id]['child'][$current_id] = &$tmp_list_data[$current_id];
            }
        }
        return $ret_tree_data;
    }

    private static function makeListTodoData($records){
        $ret_array = [];
        foreach($records as $todo_data){
            $ret_array[] = self::getTodoDataFromRecord($todo_data);
        }
        return $ret_array;
    }

    /**
     * getProjectTodoRecordsで帰ってくるレコードデータ配列の1レコードデータから、プロジェクトデータを返す
     *
     * @param $record: getProjectTodoRecordsで帰ってくるレコードデータ配列の1レコードデータ
     * @return array
     */
    private static function getProjactDataFromRecord($record){
        $ret_array = [
            'id'            => (int) $record['pj_id'],
            'name'          => $record['pj_name'],
            'view_order'    => $record['pj_view_order'],
            'user_id'       => $record['pj_user_id'],
            'created'       => $record['pj_created'],
            'modified'      => $record['pj_modified'],
        ];
        return $ret_array;
    }

    /**
     * getProjectTodoRecordsで帰ってくるレコードデータ配列の1レコードデータから、Todoデータを返す
     *
     * @param $record: getProjectTodoRecordsで帰ってくるレコードデータ配列の1レコードデータ
     * @return array
     */
    private static function getTodoDataFromRecord($record){
        foreach($record as $key => &$value){
            if(in_array($key, ['id', 'project_id', 'user_id'])){
                $value = (int) $value;
            }
        }
        return $record;
    }

    /**
     * recordsデータから、プロジェクトとtodoリストのデータを返す。
     * $user_idのみ渡した時：そのユーザーの全てのプロジェクトと、それに紐づくTodoデータのrecordデータ配列を返す
     * $start_dataも渡された時は、プロジェクト毎にその日にdo_data(着手日)かlimit_data(締切日)が設定されているTodoデータのrecordデータ配列を返す
     * $limit_dataも渡された時は、$start_dataから$limit_dateまでの範囲で上記のデータを返す。$limit_dateのみ渡されていた場合は無視される
     * todo: 引数名$limit_dateがTodoのカラム名とかぶっていてややこしい
     *
     * @param $user_id
     * @param null $start_date :
     * @param null $limit_date
     * @param null $project_id //指定プロジェクトidで絞り込む
     * @return array
     */
    private static function getProjectTodoRecords($user_id, $start_date = null, $limit_date = null, $project_id = null){
        new Todo();
        $get_data_mode = (isset($start_date)) ? "date" : "all";//all or date

        $params = [];
        $where_sql = "WHERE td.user_id = :user_id ";
        $params['user_id'] = $user_id;
        if($project_id){
            $where_sql .= "AND td.project_id = :project_id ";
            $params['project_id'] = $project_id;
        }
        if($get_data_mode == "date"){
            if(is_null($limit_date)){
                $where_sql .="AND td.do_date = :start_date "
                    . "OR td.limit_date = :start_date ";
            }else{
                $where_sql .="AND td.do_date BETWEEN :start_date AND :limit_date "
                    . "OR td.limit_date BETWEEN :start_date AND :limit_date ";
            }
            $params[':start_date'] = $start_date;
            $params[':limit_date'] = $limit_date;
        }
        $order_sql = "ORDER BY ";
        if($get_data_mode == "date"){
            $order_sql .= "CASE WHEN td.do_date BETWEEN :start_date AND :limit_date THEN td.do_date ELSE td.limit_date END ASC, ";
        }
        $order_sql .= "pj.view_order ASC, "
                     ."(LENGTH(path) - LENGTH(REPLACE(path, '/', '')) -1) ASC ";

        //sqlの組み立て
        $sql = "SELECT
                    td.id,
                    td.title,
                    td.do_date,
                    td.limit_date,
                    td.is_done,
                    td.path,
                    td.project_id,
                    td.user_id,
                    td.created,
                    td.modified,
                    pj.id as pj_id,
                    pj.name as pj_name,
                    pj.view_order as pj_view_order,
                    pj.user_id as pj_user_id,
                    pj.created as pj_created,
                    pj.modified as pj_modified
                FROM todo td
                    INNER JOIN project pj ON pj.id = td.project_id
                ${where_sql}
                ${order_sql}";

        //sql実行
        return self::$pdo->fetchAll($sql, $params);
    }

    /**
     * todoテーブルのpathの値から、親Todo idを返す
     *
     * @param $path
     * @return int
     */
    private static function convertParentIdByPath($path){
        $path_array = explode("/", $path);
        return (int) $path_array[count($path_array) - 3];
    }
}