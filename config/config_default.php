<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/29
 * Time: 14:20
 *
 * 設定ファイル
 */

$db = [
    'host' => "host_name",
    'user' => "user_name",
    'password' => "password",
    'db' => "db_name",
];
$db = [
    'test' => [
        'host' => "host_name",
        'user' => "user_name",
        'password' => "password",
        'db' => "db_name",
    ],
    'unit_test' => [
        'host' => "host_name",
        'user' => "user_name",
        'password' => "password",
        'db' => "db_name",
    ]
];

return [
    "db" => $db,
];