<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/29
 * Time: 14:20
 *
 * 設定ファイル
 */

$db = array(
    'host' => "host_name",
    'user' => "user_name",
    'password' => "password",
    'db' => "db_name",
);
$db = array(
    'test' => array(
        'host' => "host_name",
        'user' => "user_name",
        'password' => "password",
        'db' => "db_name",
    ),
    'unit_test' => array(
        'host' => "host_name",
        'user' => "user_name",
        'password' => "password",
        'db' => "db_name",
    )
);

return array(
    "db" => $db,
);