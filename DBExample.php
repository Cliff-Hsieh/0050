<?php

class DBController{

    public function connect(){
        $db_host = 'localhost';
        $db_user = 'dbname';
        $db_pass = 'dbpassword';
        $db_name = 'stocks';
        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        return $conn;
    }
}
