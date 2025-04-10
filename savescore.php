<?php
$data = json_decode(file_get_contents("php://input"), true);

$line = "{$data['nickname']},{$data['level']},{$data['time']}\n";
file_put_contents('leaderboard.csv', $line, FILE_APPEND);
?>
