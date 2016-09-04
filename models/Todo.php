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
    private $title;
    private $do_date;
    private $limit_date;
    private $is_done;
    private $path;
    private $project_id;
    private $user_id;

    public function __construct($id = null){
        parent::__construct();
        if($id){
            $sql = "SELECT * FROM todo WHERE id = :id";
            $result = self::$pdo->fetch($sql, [':id' => $id]);
            if($result){
                $this->loadArray($result);
                $this->created = $result['created'];
                $this->modified = $result['modified'];
            }
        }
    }

    public function __set($name, $value){
        if(in_array($name, ['id', 'title', 'do_date', 'limit_date', 'is_done', 'path', 'project_id', 'user_id'])){
            $this->$name = (in_array($name, ['id', 'project_id'])) ? (int) $value : $value;
        }
    }

    public function __get($name){
        return $this->$name;
    }

    public function loadArray(array $properties){
        foreach($properties as $name => $value){
            $this->__set($name, $value);
        }
    }

    public function getArray(){
        $ret_array = [
            'id' => $this->id,
            'title' => $this->title,
            'do_date' => $this->do_date,
            'limit_date' => $this->limit_date,
            'is_done' => $this->is_done,
            'path' => $this->path,
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'created' => $this->created,
            'modified' => $this->modified,
        ];
        return $ret_array;
    }

    public function validate(){
        $error_msg = [];
        $error_msg['id'] = $this->validateId();
        $error_msg['title'] = $this->validateTitle();
        $error_msg['do_date'] = $this->validateDoDate();
        $error_msg['limit_date'] = $this->validateLimitDate();
        $error_msg['is_done'] = $this->validateIsDone();
        $error_msg['path'] = $this->validatePath();
        $error_msg['user_id'] = $this->validateUserId();
        $error_msg['project_id'] = $this->validateProjectId();
        return $error_msg;
    }

    public function validateId(){
        $error_msg = [];
        //idはintで必須。新規登録の場合は-1が入る
        if(!(isset($this->id) && is_numeric($this->id))){
            $error_msg[] = "idは数字で指定してください";
        }
        return $error_msg;
    }

    public function validateTitle(){
        $error_msg = [];
        //titleは文字列で必須
        if(!(isset($this->title) && is_string($this->title) && $this->title != "")){
            $error_msg[] = "Todo名を指定してください";
        }
        return $error_msg;
    }

    public function validateDoDate(){
        $error_msg = [];
        //do_dateは日付
        if(isset($this->do_date) && $this->do_date != date("Y-m-d", strtotime($this->do_date))){
            $error_msg[] = "正しい日付を指定してください";
        }
        return $error_msg;
    }

    public function validateLimitDate(){
        $error_msg = [];
        //limit_dateは日付
        if(isset($this->limit_date) && $this->limit_date != date("Y-m-d", strtotime($this->limit_date))){
            $error_msg[] = "正しい日付を指定してください";
        }
        return $error_msg;
    }

    public function validateIsDone(){
        $error_msg = [];
        //is_doneはDONE or UNDONE。
        if($this->is_done != "DONE" && $this->is_done != "UNDONE"){
            $error_msg[] = "is_doneを正しく指定してください";
        }
        return $error_msg;
    }

    public function validatePath(){
        $error_msg = [];
        //pathは必須
        if(!isset($this->path)){
            $error_msg[] = "pathを正しく指定してください";
        }
        return $error_msg;
    }

    public function validateUserId(){
        $error_msg = [];
        //user_idは必須で数値
        if(!is_numeric($this->user_id)){
            $error_msg[] = "ユーザーidを正しく指定してください";
        }
        return $error_msg;
    }

    public function validateProjectId(){
        $error_msg = [];
        //project_idはintで必須 -1はプロジェクトを同時に登録するときに入る。user_idが正しく入力されている場合は、そのユーザーのプロジェクトである
        if(!is_numeric($this->project_id)){
            $error_msg[] = "プロジェクトidを正しく指定してください";
        }elseif($this->project_id != -1 && !isset($error_msg['user_id'])){
            $project = new Project($this->project_id);
            if($project->user_id != $this->user_id){
                $error_msg[] = "プロジェクトidを正しく指定してください";
            }
        }
        return $error_msg;
    }

    public function validationPath(){
        $error_msg = [];
        //パスの構成が正しいかチェック。
        //構成するidのTodoデータ($thisも含めて)のプロジェクトid,user_idが全て同一であるはず。
        $todo_ids = explode("/", trim($this->path, "/"));
        if(isset($this->id)){
            //pathの末のidは$this->idのはず。
            $tmp_id = array_pop($todo_ids);
            if($this->id != $tmp_id){
                $error_msg[] = "pathがただしくありません";
                return $error_msg;
            }
        }
        //全てのidのtodoオブジェクトを作成する
        $todo_objects = self::getTodosByIds($todo_ids);
        $project_ids = [];
        $user_ids = [];
        foreach($todo_objects as $todo_obj){
            $project_ids[] = $todo_obj->project_id;
            $user_ids[] = $todo_obj->user_id;
        }

        //todo:リファクタできそう
        $uniqued_project_ids = array_unique($project_ids);
        if(!(count($uniqued_project_ids) == 1 && current($uniqued_project_ids) == $this->project_id)){
            $error_msg[] = "pathがただしくありません";
            return $error_msg;
        }
        $uniqued_user_ids = array_unique($user_ids);
        if(!(count($uniqued_user_ids) == 1 && current($uniqued_user_ids) == $this->user_id)){
            $error_msg[] = "pathがただしくありません";
            return $error_msg;
        }
        return $error_msg;
    }

    public static function getTodosByProjectIds(array $project_ids){
        $ret_array = [];
        $clause = implode(",", array_fill(0, count($project_ids), '?'));
        $sql = "SELECT td.* FROM todo td "
              ."INNER JOIN project pj ON pj.id = td.project_id "
              ."WHERE project_id IN (".$clause.") "
              ."ORDER BY pj.view_order ASC, "
              ."(LENGTH(path) - LENGTH(REPLACE(path, '/', '')) -1) ASC ";
        new Todo();
        foreach(self::$pdo->fetchAll($sql, $project_ids) as $todo){
            $todo_obj = new Todo();
            $todo_obj->loadArray($todo);
            $todo_obj->created = $todo['created'];
            $todo_obj->modified = $todo['modified'];
            $ret_array[$todo_obj->id] = $todo_obj;
        }
        return $ret_array;
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
     * プロジェクトid配列から、すべてのTodoデータを返す
     *
     * @param array $project_ids
     * @return array
     */
    public static function getTodoByProjectId(array $project_ids){
        $clause = implode(',', array_fill(0, count($project_ids), '?'));
        $sql = "SELECT * FROM todo WHERE project_id IN (".$clause.") "
              ."ORDER BY (LENGTH(path) - LENGTH(REPLACE(path, '/', '')) -1) ASC ";

        new Todo();
        $ret_array = [];
        foreach(self::$pdo->fetchAll($sql, $project_ids) as $todo_record){
            $ret_array[] = self::castIntTodoRecord($todo_record);
        }
        return $ret_array;
    }

    /**
     * @param array $ids todo_idの配列
     * @return Todo[]
     */
    public static function getTodosByIds(array $ids){
        if(empty($ids)){
            return [];
        }
        $in_array = array_fill(0, count($ids), "?");
        $sql = "SELECT * FROM todo WHERE id IN (".implode(",", $in_array).") ";
        new Todo();
        if(!$result = self::$pdo->fetchAll($sql, $ids)){
            return [];
        }
        $ret_array = [];
        foreach($result as $data){
            $tmp_todo = new Todo();
            $tmp_todo->loadArray($data);
            $ret_array[] = $tmp_todo;
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
        $target_todo_obj = self::getTodo($id, $user_id);
        $target_todo = $target_todo_obj->getArray();
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

        new Todo();
        //project_rootは変更できないようにする//todo: project_rootの判断を文字列で行っている。tableにカラムを追加してパラメーターで判断するようにしたほうがいい
        $project_root_check_sql = "SELECT title FROM todo WHERE id = :id ";
        $result = self::$pdo->fetch($project_root_check_sql, [':id' => $id]);
        if($result['title'] == "project_root"){
            throw new \Exception("project_rootは変更できません");
        }

        //project_idからproject_rootを取得する。
        $project_root_id = $project_data['root_todo_id'];

        //内容保存処理
        $sql = "UPDATE todo SET title=:title, do_date=:do_date, limit_date=:limit_date, path=:path, project_id=:project_id "
              ."WHERE id = :id AND user_id = :user_id";
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':do_date' => $do_date,
            ':limit_date' => $limit_date,
            ':path' => "/${project_root_id}/${id}/",
            ':project_id' => $project_id,
            ':user_id' => $user_id,
        ];
        self::$pdo->execute($sql, $params);
        return $error_msg;
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
     * project_root_todoを作成して、そのidを返す
     * @param $project_id
     * @param $user_id
     * @return
     */
    static public function newRootTodo($project_id, $user_id){
        new Todo();
        $get_next_insert_id_sql = "SHOW TABLE STATUS LIKE 'todo' ";
        $result = self::$pdo->fetch($get_next_insert_id_sql);
        $next_insert_id = $result['Auto_increment'];

        $make_root_todo_sql = "INSERT INTO todo (id, title, path, project_id, user_id, created) VALUE (:id, :title, :path, :project_id, :user_id, :created) ";
        self::$pdo->execute($make_root_todo_sql, [
            ':id' => $next_insert_id,
            ':title' => "project_root",
            ':path' => "/".$next_insert_id."/",
            ':project_id' => $project_id,
            ':user_id' => $user_id,
            ':created' => date("Y-m-d H:i:s"),
        ]);
        return $next_insert_id;
    }

    /**
     * todo_idとuser_idから対応するtodoデータのレコードを返す
     *
     * @param $id
     * @param $user_id
     * @return array
     */
    static public function getTodo($id, $user_id){
        $todo = new Todo();
        $sql = "SELECT * FROM todo WHERE id = :id AND user_id = :user_id";
        $todo_data = self::$pdo->fetch($sql, [':id' => $id, 'user_id' => $user_id]);
        if($todo_data){
            $todo->loadArray($todo_data);
            $todo->created = $todo_data['created'];
            $todo->modified = $todo_data['modified'];
            return $todo;
        }else{
            return false;
        }
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
    public static function makeTreeData($records){
        $ret_tree_data = [];//返却用Tree構造データ
        $tmp_list_data = [];//Treeデータ作成に必要なlistデータ
        foreach($records as $todo_data){
            $path_array = array_filter(explode('/', $todo_data['path']), 'strlen');
            $current_id = array_pop($path_array);
            $parent_id = array_pop($path_array);
            $tmp_list_data[$current_id] = [
                'data' => self::castIntTodoRecord($todo_data),
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

    /**
     * @param $user_id
     * @param $start_date
     * @param $limit_date
     * @return array
     */
    public static function getTodoByDate($user_id, $start_date, $limit_date){
        $sql = "SELECT td.* FROM todo td "
              ."INNER JOIN project pj ON pj.id = td.project_id "
              ."WHERE td.user_id = :user_id "
              ."AND td.do_date BETWEEN :start_date AND :limit_date "
              ."OR td.limit_date BETWEEN :start_date AND :limit_date "
              ."ORDER BY CASE WHEN td.do_date BETWEEN '2016-07-20' AND '2016-07-21' THEN td.do_date ELSE td.limit_date END ASC, "
              ."pj.view_order ASC ";
        new Todo();
        $result = self::$pdo->fetchAll($sql, [
            ':user_id' => $user_id,
            ':start_date' => $start_date,
            ':limit_date' => $limit_date,
        ]);

        $ret_array = [];
        foreach($result as $data){
            $todo = new Todo();
            $todo->loadArray($data);
            $todo->created = $data['created'];
            $todo->modified = $data['modified'];
            $ret_array[$todo->id] = $todo;
        }
        return $ret_array;
    }

    private static function makeListTodoData($records){
        $ret_array = [];
        foreach($records as $todo_data){
            $ret_array[] = self::castIntTodoRecord($todo_data);
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
     * Todoテーブルから取得するレコード配列を渡すと、数値カラムの値はint型に変換して返す
     *
     * @param $record: Todoテーブル配列の1レコードデータ
     * @return array
     */
    private static function castIntTodoRecord($record){
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
     * pathの値から、親Todo idを切り出して返す
     *
     * @return int
     */
    public function getParentId(){
        $path_array = explode("/", $this->path);
        return (int) $path_array[count($path_array) - 3];
    }

    /**
     * 親todo_idから$pathをセットする。$this->idがあれば、自分のidを最後に付加する。ない場合は、保存するタイミングで決定されるidが付加される。
     * $this->id
     * @param $parent_todo_id
     */
    public function setPathByParentTodoId($parent_todo_id){
        $todo = new Todo($parent_todo_id);
        $this->path = $todo->path;
        if(isset($this->id)){
            $this->path .= $this->id."/";
        }
    }
}