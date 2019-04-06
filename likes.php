<?php
session_start();
/**
 * Created by PhpStorm.
 * User: instantech
 * Date: 09/03/19
 * Time: 11:18
 */

require_once 'bdd.php';
require_once 'functions.php';
//Si l'utilisateur est connecté, on le redirige à la page de connexion
if (is_null(get_session('is_authentificate'))){
    try {
        redirect('./connexion.php');
    } catch (Exception $e) {
    }
}

//On recupere l'id de l'article à liker
//La fonction purge nétoie la variable en s'assurant qu'on a pas d'injection
//Cela nous permet d'avoir un nombre ou null, si l'utilisateur tapaait n'imporque quoi dans l'url
$articles_id = (isset($_GET['article_id'])  &&
    (int)purge($_GET['article_id']) > 0 ) ?
    purge($_GET['article_id']) : null;

//On verifie si l'id de l'article est différent de null,
// sinon on arrête tout, pas la peine de continuer
try {
    (!is_null($articles_id)) ?
        //On appelle notre fonction de like en lui passant l'id de notre article, à liker
        $error = likes_articles_by_id($articles_id) : exit();
} catch (Exception $e) {
    $error = $e->getMessage();
}

//On verifie si tout ne s'est bien dérouler, alors on affiche le message d'erreur de retour,
// sinon on fait une redirection vers la page des artiles
if (!is_null($error))
    echo $error;
else
    try {
        redirect('./');
    } catch (Exception $e) {
    }
