<?php
/**
 * Created by PhpStorm.
 * User: instantech
 * Date: 09/03/19
 * Time: 11:18
 */
try{
    $bdd = new PDO('mysql:host=localhost;dbname=sys_likes;charset=utf8',
        'root','', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}catch (PDOException $exception){
    echo $exception;
}
