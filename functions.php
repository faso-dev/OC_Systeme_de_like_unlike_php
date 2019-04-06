<?php
/**
 * Created by PhpStorm.
 * User: instantech
 * Date: 09/03/19
 * Time: 11:18
 */
require_once 'bdd.php';
/**
 * Liker un article à partir de son id et celui de l'utilisateur
 * @param $article_id
 * @return string|null
 * @throws Exception
 */
function likes_articles_by_id($article_id){
    $error = null;
    //verifier si cet article à deja un like de cet utilisateur, si oui on le retire
    if (is_null(get_session('is_authotificate')))
        redirect('./connexion');
    $likes_data = findBy(['likes'],'COUNT(id) as is_like, id as id_like','likes.user_id = '.get_session('user_id').' AND likes.article_id = '.$article_id);
    if (!is_null($likes_data) && $likes_data->is_like == 1){
        try {
            remove_like_by_id($likes_data->id_like);
        } catch (Exception $e) {
            $error = 'Une erreur interne est survenue lors de la suppresion du like ';
        }
    }else{
        //Sinon on ajoute le like
        try {
            add_like($article_id);
        } catch (Exception $e) {
            $error = 'Une erreur est survenue lors du like ';
        }
    }
    return $error;
}

/**
 * Supprimer un like attribuer à un article
 * @param $like_id
 * @throws Exception
 */
function remove_like_by_id($like_id){
    delete('likes',['id' => $like_id],['=']);
}

/**
 * Ajoute le like à l'article
 * @param $article_id
 * @throws Exception
 */
function add_like($article_id){
    insertInTo('likes',[
        'article_id' => $article_id,
        'user_id' => get_session('user_id')
    ]);
}
/**
 * Permet de rechercher un like à travers son utilisateur et l'article
 * @param array $tables
 * @param $field
 * @param $condition
 * @return mixed|null
 */
function findBy(array $tables, $field, $condition ){
    global $bdd;
    $query = 'SELECT '.$field.' FROM '.implode(',',$tables).' WHERE '.$condition;
    $request = $bdd->query($query);
    $response = $request->fetch(PDO::FETCH_OBJ);
    return (null != $response)? $response : null;
}

/**
 * Echappe la donnée des balises <></>
 * @param $form_field
 * @return string
 */
function purge($form_field){
    return htmlspecialchars($form_field);
}

/**
 * Supprime une donnée ou des données dans la base de données suivant les conditions
 * @example delete('user',array(
 * 'age => 10,
 *  'sexe' => 'masculin'
 * ),'
 * array('<','='),
 * or');
 * @param $table_name
 * @param $criterias
 * @param $operators
 * @param null $condition
 * @return bool
 * @throws Exception
 */
function delete($table_name, $criterias, $operators, $condition = null)
{
    global $bdd;
    $query = 'DELETE FROM ' . $table_name . ' WHERE ' . create_querySelect($criterias, $operators, $condition);
    echo $query;
    $requette = $bdd->prepare($query);
    $nblignes = $requette->execute(getTableValues($criterias));
    return $nblignes;
}
/**
 * @param $criterias
 * @param $operators
 * @param $conditions
 * @return string
 * @throws Exception
 */
function create_querySelect($criterias, $operators, $conditions = null)
{
    $query = '';
    $fields_conditions_keys = getTableColums($criterias);
    $fields_conditions_question = getPrepareNumbersFields($criterias);
    $operators_count = count($operators);
    $operators_value = getTableValues($operators);
    $length = count($criterias);
    if (!is_null($conditions)) {
        if (in_array(strtoupper($conditions), array('OR', 'AND',))) {
            for ($i = 0; $i < $length - 1; $i++) {
                if ($operators_count == 1)
                    $query .= $fields_conditions_keys[$i] . ' ' . $operators_value[0] . ' ' . $fields_conditions_question[$i] . ' ' . strtoupper($conditions) . ' ';
                else
                    $query .= $fields_conditions_keys[$i] . ' ' . $operators_value[$i] . ' ' . $fields_conditions_question[$i] . ' ' . strtoupper($conditions) . ' ';
            }
            $query .= $fields_conditions_keys[$length - 1] . ' ' . $operators_value[$operators_count - 1] . ' ' . $fields_conditions_question[$length - 1];
        } else
            throw new Exception("Argument invalid : accepted paramaters => and, or");
    } else
        $query .= $fields_conditions_keys[$length - 1] . ' ' . $operators_value[0] . ' ' . $fields_conditions_question[$length - 1];
    return $query;
}

/**
 * @param array $data
 * @return array
 * @throws Exception
 */
function getTableColums(array $data)
{
    if (empty($data))
        throw new Exception("Vous n'avez pas fournis les noms des champs de la table");
    $colums = [];
    foreach ($data as $colum => $value) {
        $colums[] = $colum . ' ';
    }
    return $colums;
}

/**
 * @param array $data
 * @return array
 * @throws Exception
 */
function getTableValues(array $data)
{
    if (empty($data))
        throw new Exception("Vous n'avez pas fournis les valeus des champs de la table");
    $values = [];
    foreach ($data as $colum => $value) {
        $values[] = $value . ' ';
    }
    return $values;
}

/**
 * @param $data
 * @return array
 */
function getPrepareNumbersFields($data)
{
    $numberFields = count($data);
    return explode(',', str_repeat("?,", $numberFields - 1) . "?");
}
/**
 * @param $name
 * @return mixed
 */
function get_session($name)
{
    if (isset($_SESSION) && isset($_SESSION[$name]))
        return $_SESSION[$name];
    return null;
}

/**
 * Créer une session avec plusieurs paramètres et valeurs
 * @example ( make_sessions(array('username' => 'instantech', 'mail' => 'instantech@gmail.com')
 * @param array $options
 * @throws Exception
 */
function make_sessions(array $options)
{
    if (!isset($options) || empty($options))
        throw new Exception("Vous devez fournir les valeurs cle => valeur pour creer la session ");
    foreach ($options as $name => $value) {
        $_SESSION[$name] = $value;
    }
}

/**
 * Recoit une url vers laquelle serait rediriger l'utilisateur si besoin
 * @example destroy_session('connexion.php')
 *Detruit une session
 * @param null $redirectTo
 * @throws Exception
 */
function destroy_session($redirectTo = null)
{
    session_destroy();
    if (!is_null($redirectTo))
        redirect($redirectTo);
}

/**
 * @example redirect('accueil.php')
 * Redirige vers l'url recu en paramètre
 * @param $urlTo
 * @throws Exception
 */
function redirect($urlTo)
{
    /*if (!file_exists($urlTo))
        throw new Exception("L'url que vous avez fourni n'est pas valide ");*/
    header('Location:' . $urlTo);
}

/**
 * Deconnecte un utilisateur et le redirige vers la page passée en argument
 * @example logout('connexion.php')
 * @param $urlTo
 * @throws Exception
 */
function logout($urlTo)
{
    destroy_session($urlTo);
}

/**
 * Connecte un utilisateur en le redirigeants vers la page spécifier en argument
 * @example login('home.php or home.php or accueil.php')
 * @param $urlTo
 * @param array $options
 * @throws Exception
 */
function login($urlTo, array $options = [])
{
    if (!empty($options)) {
        make_sessions($options);
        redirect($urlTo);
    } else
        throw new Exception("Vous devez fournir une liste de cle => valeur pour la création de la session");
}

/**
 * @example insertInTo('clients',
 *    array('name' => 'instantech',
 *          'mail' => 'instantech@mail.com',
 *          'username' => 'instantech28'))
 * @param $table_name
 * @param array $data
 * @return string
 * @throws Exception
 */
function insertInTo($table_name, array $data)
{
    global $bdd;
    $query = 'INSERT INTO ' . $table_name . ' (' . implode(',', getTableColums($data)) . ') VALUES (' . implode(',', getPrepareNumbersFields($data)) . ')';
    $requette = $bdd->prepare($query);
    $nbline_affeted = $requette->execute(getTableValues($data));
    return $nbline_affeted;
}
/**
 * Genere un mot de passe de 256 bit
 * @example  $password = creat_hashed_password('instantech@123!@')
 * @param $password
 * @return string
 */
function creat_hashed_password($password)
{
    return hash('sha256', 'instantech' . purge($password) . 'instantech');
}
/**
 * connecte un utilisateur sur le blog
 * @return mixed|string|null
 */
function user_loged()
{
    $error = null;
    $route = './';
    if (isset($_POST['mail'],$_POST['password']) && !empty($_POST['mail']) && !empty($_POST['password'])) {
        try {
            $user = userSelect('user', array(
                'user.id', 'mail', 'password'
            ), array('mail' => purge($_POST['mail']),
                'password' => purge($_POST['password'])), array('='), 'and');
            if ($user['exist'] == 1) {
                login($route, array(
                    'is_authentificate' => 'oui',
                    'user_id' => $user['id'],
                    'usermail' => $user['mail'],
                ));
            } else
                $error = "Nous n'avons pas trouver un utilisateur avec ses identifiants !";
        } catch (Exception $e) {
            $error = 'Une erreur est survenue lors de votre connexion ';
        }

    } else {
        $error = "Vous devez renseigner le formulaire";
    }
    return $error;
}

/**
 * Selectionne et renvoie toutes les données d'une table
 * @example $resultat = findByWhere('user',
 *     array('user.name','user.mail','user.password')
 *     array('name' => 'instantech',
 *           'email' => 'instantech@mail.com),
 *     array('='),
 *           'and')
 * @param $table_name
 * @param array $dataFields
 * @param array $criterias
 * @param $operators
 * @param $conditions
 * @return array
 * @throws Exception
 */
function userSelect($table_name, array $dataFields, array $criterias, $operators, $conditions = null)
{
    global $bdd;
    $query = 'SELECT COUNT(id) as exist,' . implode(',', $dataFields) . ' FROM ' . $table_name . ' WHERE ' . create_querySelect($criterias, $operators, $conditions);
    $requette = $bdd->prepare($query);
    $requette->execute(getTableValues($criterias));
    $resultats = $requette->fetch();
    return $resultats;

}
/**
 * @param $article_id
 * @return int
 */
function count_all_likes_by_article_id($article_id){
    $likes = findBy(['likes'],'COUNT(id) as nb_likes','likes.article_id = '.$article_id);
    if (!is_null($likes) && $likes->nb_likes > 0)
        return $likes->nb_likes;
    return 0;
}
