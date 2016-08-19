<?php
/**
 * PDOのラッパークラス
 *
 * Created by PhpStorm.
 * User: mix
 * Date: 2016/07/28
 * Time: 20:36
 */

namespace classes;


class UserPdo extends \PDO
{
    /**
     * @param $sql
     * @param array $params
     * @return array
     */
    public function fetch($sql, array $params = array()){
        return $this->executeSql($sql, $params, "fetch_one");
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, array $params = array()){
        return $this->executeSql($sql, $params, "fetch_all");
    }

    /**
     * @param $sql
     * @param array $params
     * @return bool
     */
    public function execute($sql, array $params = array()){
        return $this->executeSql($sql, $params, "execute_only");
    }

    /**
     * @param $sql
     * @param array $params
     * @param string $mode
     * @return array|bool|mixed
     */
    private function executeSql($sql, array $params = array(), $mode = 'execute_only'){
        if(!in_array($mode, array('fetch_one', 'fetch_all', 'execute_only'))){
            throw new \PDOException("fetch_modeはoneかallに指定してください");
        }

        if(empty($params)){
            $stmt = $this->query($sql);
        }else{
            $stmt = $this->prepare($sql);
        }
        if(!$stmt || !$stmt->execute($params)){
            throw new \PDOException("DBデータ取得に失敗しました");
        }

        switch($mode){
            case "fetch_one":
                $ret_data = $stmt->fetch(\PDO::FETCH_ASSOC);
                break;
            case "fetch_all":
                $ret_data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                break;
            case "execute_only":
                $ret_data = true;
                break;
            default:
                throw new \PDOException("不明なエラーが発生しました");
                break;
        }
        return $ret_data;
    }
}