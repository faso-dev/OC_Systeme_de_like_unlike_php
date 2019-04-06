<?php
session_start();

require_once 'bdd.php';
require_once 'functions.php';
//Si l'utilisateur est connecté, on le redirige à la page d'accueil
if (!is_null(get_session('is_authentificate'))){
    try {
        redirect('./');
    } catch (Exception $e) {
    }
}

if (isset($_POST['login'])) {
    $error = user_loged();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Connexion</title>
</head>
<body>
    <div>
        <?php if (isset($error) && !is_null($error)) : ?>
            <h2 style="background: red; padding: 30px;color: white"><?= $error ?></h2>
        <?php endif; ?>
        <form action="" method="post" id="form">
            <input type="text" name="mail" id="" placeholder="Enter your mail.." value="<?= isset($_POST['mail']) ? $_POST['mail'] : '' ?>">
            <input type="password" name="password" id="" placeholder="Enter your password...">
            <input type="submit" value="Login" name="login">
        </form>
    </div>
</body>
</html>
