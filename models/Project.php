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