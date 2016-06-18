<?php
/**
 * Created by IntelliJ IDEA.
 * User: mix
 * Date: 2016/05/24
 * Time: 22:34
 */
?>
<?php foreach($todo_data_list as $todo_data): ?>
    <ul>
        <li><?= $todo_data['title'] ?></li>
    </ul>
<?php endforeach; ?>
