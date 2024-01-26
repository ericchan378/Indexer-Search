<?php
    //saves output of any data till the end
    ob_start(); // output buffering
    
    try{

        // dbName, host, username, password
        $connection = new PDO("mysql:dbname=searchdb; host=localhost", "root", "");

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    }
    catch(PDOException $e){  
        echo "Connection Failed: " . $e->getMessage();
    }
?>
