<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['share_list_id']) && isset($_POST['share_user_id'])) {
    $share_list_id = $_POST['share_list_id'];
    $share_user_id = $_POST['share_user_id'];

    $sql =