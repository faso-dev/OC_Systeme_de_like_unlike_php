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

$query = 'SELECT * FROM article WHERE 1';
$request = $bdd->query($query);
$articles = $request->fetchAll(PDO::FETCH_OBJ);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>La liste de mes articles </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>Voici la liste de nos articles</h1>
<div class="container">
    <?php foreach ($articles as $article) : ?>
       <div class="article">
           <h4 class="title"><?= $article->title ?></h4>
           <p class="content">
               <?= $article->content ?>
           </p>
           <a href="likes.php?article_id=<?= $article->id ?>" class="liker">Liker (<?= count_all_likes_by_article_id($article->id) ?>)</a>
       </div>
        <hr>
    <?php endforeach; ?>
</div>
</body>
</html>
