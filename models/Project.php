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
    private $name;
    private $view_order;
    private $user_id;
    private $root_todo_id;

    /**
     * Project constructor.
     * @param null $id
     */
    public function __construct($id = null){
        parent::__construct();
        if($id){
            $sql = "SELECT * FROM project WHERE id = :id";
            $result = self::$pdo->fetch($sql, [':id' => $id]);
            if($result){
                $this->loadArray($result);
                $this->created = $result['created'];
                $this->modified = $result['modified'];
            }
        }
    }

    public function __set($name, $value){
        if(in_array($name, ['id', 'name', 'view_order', 'user_id', 'root_todo_id'])){
            if(!is_null($value)){
                $this->$name = (in_array($name, ['id', 'view_order', 'user_id', 'root_todo_id'])) ? (int) $value : $value;
            }
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

    public function validate(){
        $error_msg = [];
        $error_msg['id'] = $this->validateId();
        $error_msg['name'] = $this->validateName();
        $error_msg['view_order'] = $this->validateViewOrder();
        $error_msg['user_id'] = $this->validateUserId();
        $error_msg['root_todo_id'] = $this->validateRootTodoId();
        foreach($error_msg as $key => $message){
            if(empty($message)){
                unset($error_msg[$key]);
            }
        }
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
    public function validateName(){
        $error_msg = [];
        if(!(isset($this->name) && is_string($this->name) && $this->name != "")){
            $error_msg[] = "プロジェクト名を指定してください";
        }
        return $error_msg;
    }
    public function validateViewOrder(){
        $error_msg = [];
        //viewOrderは任意。数値。入力されなければ、自動でいれる
        if(isset($this->view_order) && !is_numeric($this->view_order)){
            $error_msg[] = "並び順を指定してください";
        }
        return $error_msg;
    }
    public function validateUserId(){
        $error_msg = [];
        //user_idはintで必須
        if(!(isset($this->user_id) && is_numeric($this->user_id))){
            $error_msg[] = "ユーザーidを指定してください";
        }
        return $error_msg;
    }
    public function validateRootTodoId(){
        $error_msg = [];
        //root_todo_idが-1(新しいproject登録で一時的に必要)か、そうでない場合はそのtodoのpathの深さが1で、プロジェクトidが$this->idのtodo_idでなければならない
        if($this->root_todo_id != -1){
            $todo = new Todo($this->root_todo_id);
            if(is_null($todo->id) || $todo->getPathDepth() != 1 || $todo->project_id != $this->id){
                $error_msg[] = "ルートTodo idがただしくありません";
            }
        }
        return $error_msg;
    }

    /**
     * @return array
     */
    public function save(){
        $error_msg = $this->validate(); //バリデーション
        if(empty($error_msg)){
            if(is_null($this->view_order)){
                $this->setNextViewOrder();
            }
            if(is_null($this->id)){
                //新規登録
                $sql = "INSERT INTO project (name, view_order, user_id, root_todo_id, created) "
                      ."VALUES (:name, :view_order, :user_id,:root_todo_id, :created ) ";
                $params[':created'] = $this->created = date("Y-m-d H:i:s");
            }else{
                //更新
                $sql = "UPDATE project SET name = :name, view_order = :view_order, user_id = :user_id, root_todo_id = :root_todo_id "
                      ."WHERE id = :id ";
                $params[':id'] = $this->id;
            }
            $params = array_merge($params, [
                ':name' => $this->name,
                ':view_order' => $this->view_order,
                ':user_id' => $this->user_id,
                ':root_todo_id' => $this->root_todo_id,
            ]);
            $this->begin();
            $this->db->execute($sql, $params);
            if(is_null($this->id)){
                $this->id = $this->db->lastInsertId("id");
            }
            //todo: 更新時間を取得する方法をしらべる。
            $this->commit();
        }
        return $error_msg;
    }

    /**
     * 同じuser_idのプロジェクトのview_orderの最大値の+100を$this->view_orderにセットする
     */
    private function setNextViewOrder(){
        $max_view_order_sql = "SELECT MAX(view_order) as max_view_order FROM project WHERE user_id = :user_id GROUP BY user_id ";
        $result = $this->db->fetch($max_view_order_sql, [':user_id' => $this->user_id]);
        $max_view_order = ($result === false) ? 0 : (int) $result['max_view_order'];
        $this->view_order = $max_view_order + 100;
    }

    static public function getProjectsById(array $ids){
        $ret_array = [];
        $clause = implode(", ", array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM project WHERE id IN (".$clause.") ";
        new Project();
        $result = self::$pdo->fetchAll($sql, $ids);
        foreach($result as $data){
            $project = new Project();
            $project->loadArray($data);
            $project->created = $data['created'];
            $project->modified = $data['modified'];
            $ret_array[$project->id] = $project;
        }
        return $ret_array;
    }

    static public function getProjectsByUserId($user_id){
        $ret_array = [];
        $sql = "SELECT * FROM project WHERE user_id = :user_id ";
        new Project();
        foreach(self::$pdo->fetchAll($sql, [':user_id' => $user_id]) as $project_data){
            $project_obj = new Project();
            $project_obj->loadArray($project_data);
            $project_obj->created = $project_data['created'];
            $project_obj->modified = $project_data['modified'];
            $ret_array[$project_obj->id] = $project_obj;
        }
        return $ret_array;
    }

    public function getArray(){
        $ret_array = [
            'id' => $this->id,
            'name' => $this->name,
            'view_order' => $this->view_order,
            'user_id' => $this->user_id,
            'root_todo_id' => $this->root_todo_id,
            'created' => $this->created,
            'modified' => $this->modified,
        ];
        return $ret_array;
    }

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
            $project = new Project();
            $project->loadArray($record);
            $project->created = $record['created'];
            $project->modified = $record['modified'];
            $ret_array[] = $project;
        }
        return $ret_array;
    }

    static public function getProject($ids){
        $sql = "SELECT * FROM project WHERE id ";

        new Project();
        if(is_array($ids)){
            $clause = implode(',', array_fill(0, count($ids), '?'));
            $sql .= "IN (".$clause.") ";
            $res = self::$pdo->fetchAll($sql, $ids);
            foreach($res as &$record){
                $record = self::castIntProjectRecord($record);
            }
            return $res;
        }else{
            $sql .= "= ?";
            return self::castIntProjectRecord(self::$pdo->fetch($sql, [$ids]));
        }
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
     * Projectテーブルから取得するレコード配列を渡すと、数値カラムの値はint型に変換して返す
     *
     * @param $record: Projectテーブル配列の1レコードデータ
     * @return array
     */
    static private function castIntProjectRecord($record){
        foreach($record as $key => &$value){
            if(in_array($key, ['id', 'user_id'])){
                $value = (int) $value;
            }
        }
        return $record;
    }

    /**
     * user_idを渡すと、全てのプロジェクトデータを関連するTodoデータとともに返す。
     * 返却値：
     * [
     *      [
     *          'project' => [プロジェクトデータ(配列)],
     *          'todo' => [todoデータ(配列)],
     *      ],
     *      [
     *          'project' => [プロジェクトデータ(配列)],
     *          'todo' => [todoデータ(配列)],
     *      ],
     *      …
     * ]
     *
     * @param array $project_ids
     * @param bool $tree
     * @return array
     */
    public static function getProjectWithTodo(array $project_ids, $tree = false){
        $ret_data = [];

        //プロジェクトデータ取得
        $projects = self::getProject($project_ids);

        //Todoデータ取得(project_idをkeyにした配列にする)
        $todos = [];
        foreach(Todo::getTodoByProjectId($project_ids) as $todo){
            $todos[$todo['project_id']][] = $todo;
        }


        //返却データ作成
        foreach($projects as $project){
            $tmp_data = [
                'project' => $project,
                'todo' => [],
            ];
            //todoデータをのせる
            $tmp_data['todo'] = ($tree) ? Todo::makeTreeData($todos[$project['id']]) : $todos[$project['id']];
            $ret_data[] = $tmp_data;
        }
        return $ret_data;
    }
}