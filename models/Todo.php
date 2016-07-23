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



class Todo extends BaseModel
{
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
     * @return array
     * @throws \Exception
     */
    public static function getTodoListByUser($user_id){
        $records = self::getProjectTodoRecords($user_id);
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
        $ret_array = [
            'id'            => (int) $record['id'],
            'title'         => $record['title'],
            'do_date'       => $record['do_date'],
            'limit_date'    => $record['limit_date'],
            'is_done'       => $record['is_done'],
            'path'          => $record['path'],
            'project_id'    => (int) $record['project_id'],
            'user_id'       => (int) $record['user_id'],
            'created'       => $record['created'],
            'modified'      => $record['modified'],
        ];
        return $ret_array;
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
     * @return array
     * @throws \Exception
     */
    private static function getProjectTodoRecords($user_id, $start_date = null, $limit_date = null){
        new Todo();
        $get_data_mode = (isset($start_date)) ? "date" : "all";//all or date

        $params = [];
        $where_sql = "WHERE td.user_id = :user_id ";
        $params['user_id'] = $user_id;
        if($get_data_mode == "date"){
            $where_sql .="AND td.do_date BETWEEN :start_date AND :limit_date "
                       . "OR td.limit_date BETWEEN :start_date AND :limit_date ";
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
        $stmt = self::$pdo->prepare($sql);
        if(!$stmt->execute($params)){
            throw new \Exception("データ取得に失敗しました");
        }
        $records = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $records;
    }
}