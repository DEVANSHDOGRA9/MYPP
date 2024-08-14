<?php
include_once(__DIR__.'/../config.php');

header('Content-Type: application/json');
$response = [];
// Default HTTP status code

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['action']) && $_POST['action'] == 'delete_user')
    {
        $user_id = $_POST['user_id'];
        $user_id = $mysqli->real_escape_string($user_id);
        $sql = "DELETE FROM users_info WHERE id = '$user_id'";
        if($mysqli->query($sql))
        {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully';
        }
        else
        {
            $response['error'] = "Couldn't delete user";
        }
        echo json_encode($response);
    }

    if(isset($_POST['action']) && $_POST['action'] == 'delete_users')
    {
        $users = $_POST['users'];
        foreach($users as $user)
        {
            $user_id = $mysqli->real_escape_string($user['user_id']);
            $sql = "DELETE FROM users_info WHERE id = '$user_id'";
            if($mysqli->query($sql))
            {
                $response['success'] = true;
                $response['message'] = 'Users deleted successfully';
            }
            else
            {
                $response['error'][] = "Couldn't delete user ".$user['first_name'];
            }
        }
        if(!empty($response['error']))
        {
            $response['success'] = false;
        }
        echo json_encode($response);

    }
}
?>