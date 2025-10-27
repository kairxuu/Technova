<?php
    define("HOST", "localhost");
    define("USER", "root");
    define("MDP", "root");
    define("DB", "bts1aurlom");
    $conn = mysqli_connect(HOST, USER, MDP, DB);
    mysqli_set_charset($conn,'utf8');
?>