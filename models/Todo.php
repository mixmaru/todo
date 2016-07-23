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
    const TABLE_NAME = "todo";

    /**
     * ユーザーを指定して、全てのプロジェクトデータと、それに関連する全てのTodoデータを取得する
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

        $ret_data = [];
        $count = 0;
        $tmp_project_id = $records[0]['project_id'];
        $tmp_records = [];
        foreach($records as $record){
            if($tmp_project_id != $record['project_id']){
                //１プロジェクト分のデータを作成して次の処理へ進む
                $project_data = [
                    'id'            => (int) $tmp_records[0]['pj_id'],
                    'name'          => $tmp_records[0]['pj_name'],
                    'view_order'    => $tmp_records[0]['pj_view_order'],
                    'user_id'       => $tmp_records[0]['pj_user_id'],
                    'created'       => $tmp_records[0]['pj_created'],
                    'modified'      => $tmp_records[0]['pj_modified'],
                ];
                $ret_data[$count] = [
                    'project_data' => $project_data,
                    'todo_data' => self::makeTreeData($tmp_records),
                ];
                //次の処理のための処理
                $tmp_records = [];
                $tmp_project_id = $record['project_id'];
                $count++;
            }
            $tmp_records[] = $record;
        }
        if(count($tmp_records) > 0){
            $project_data = [
                'id'            => (int) $tmp_records[0]['pj_id'],
                'name'          => $tmp_records[0]['pj_name'],
                'view_order'    => $tmp_records[0]['pj_view_order'],
                'user_id'       => $tmp_records[0]['pj_user_id'],
                'created'       => $tmp_records[0]['pj_created'],
                'modified'      => $tmp_records[0]['pj_modified'],
            ];
            $ret_data[$count] = [
                'project_data' => $project_data,
                'todo_data' => self::makeTreeData($tmp_records),
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
                'data' => [
                    'id'            => (int) $todo_data['id'],
                    'title'         => $todo_data['title'],
                    'do_date'       => $todo_data['do_date'],
                    'limit_date'    => $todo_data['limit_date'],
                    'is_done'       => $todo_data['is_done'],
                    'path'          => $todo_data['path'],
                    'project_id'    => (int) $todo_data['project_id'],
                    'user_id'       => (int) $todo_data['user_id'],
                    'created'       => $todo_data['created'],
                    'modified'      => $todo_data['modified'],
                ],
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
            $ret_array[] =  [
                'id'            => (int) $todo_data['id'],
                'title'         => $todo_data['title'],
                'do_date'       => $todo_data['do_date'],
                'limit_date'    => $todo_data['limit_date'],
                'is_done'       => $todo_data['is_done'],
                'path'          => $todo_data['path'],
                'project_id'    => (int) $todo_data['project_id'],
                'user_id'       => (int) $todo_data['user_id'],
                'created'       => $todo_data['created'],
                'modified'      => $todo_data['modified'],
            ];
        }
        return $ret_array;
    }

    /**
     * 日毎Todoリスト表示用メソッド。
     * todo: sql文をgetTodoListByUserと共通化できる
     * todo: プロジェクトデータをセットする部分を共通化する
     *
     * @param $user_id
     * @param $start_date :日にちを指定。$limitを指定しない場合はこの日のTodoリストが返る
     * @param null $limit_date :日にちを指定。$start_dateから$limit_dateの間で絞り込んだTodoリストが返る
     * @return array
     * @throws \Exception
     */
    public static function getTodoListByDay($user_id, $start_date, $limit_date = null){
        $records = self::getProjectTodoRecords($user_id, $start_date, $limit_date);
        if(count($records) == 0){
            return [];
        }

        $ret_data = [];
        $count = 0;
        $tmp_project_id = $records[0]['project_id'];
        $tmp_records = [];
        foreach($records as $record){
            if($tmp_project_id != $record['project_id']){
                //１プロジェクト分のデータを作成して次の処理へ進む
                $project_data = [
                    'id'            => (int) $tmp_records[0]['pj_id'],
                    'name'          => $tmp_records[0]['pj_name'],
                    'view_order'    => $tmp_records[0]['pj_view_order'],
                    'user_id'       => $tmp_records[0]['pj_user_id'],
                    'created'       => $tmp_records[0]['pj_created'],
                    'modified'      => $tmp_records[0]['pj_modified'],
                ];
                $ret_data[$count] = [
                    'project_data' => $project_data,
                    'todo_data' => self::makeListTodoData($tmp_records),
                ];
                //次の処理のための処理
                $tmp_records = [];
                $tmp_project_id = $record['project_id'];
                $count++;
            }
            $tmp_records[] = $record;
        }
        if(count($tmp_records) > 0){
            $project_data = [
                'id'            => (int) $tmp_records[0]['pj_id'],
                'name'          => $tmp_records[0]['pj_name'],
                'view_order'    => $tmp_records[0]['pj_view_order'],
                'user_id'       => $tmp_records[0]['pj_user_id'],
                'created'       => $tmp_records[0]['pj_created'],
                'modified'      => $tmp_records[0]['pj_modified'],
            ];
            $ret_data[$count] = [
                'project_data' => $project_data,
                'todo_data' => self::makeListTodoData($tmp_records),
            ];
        }

        return $ret_data;
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