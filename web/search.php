<?php
require_once '../Lib/Database.php';
require_once '../Lib/SearchEngine.php';

$searchEngine = new SearchEngine(array(
    'resultsPerPage'    => 5,
));

$searchValue    = empty($_GET['search']) ? "Jeux stratégie joueur" : $_GET['search'];
$searchValue    = Database::getInstance()->escape_string(utf8_decode($searchValue));
var_dump($_GET['search'], $searchValue);
$searchOptions  = array(
    'forum' => empty($_GET['forum']) ? 0 : 1,
);
$searchResults  = $searchEngine->search($searchValue, $searchOptions);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <title>Moteur de recherche</title>
    </head>
    <body>
        <div id="content" style="position: relative;">
            <div>
                <form action="search.php" method="GET">
                    <input type="text" name="search" style="width: 400px;" value="<?php echo $searchValue; ?>" /> &nbsp; <input type="submit" value="Rechercher" /> <br />
                    <input type="checkbox" name="forum" value="<?php echo $_GET['forum']; ?>" /> <i>Exclure les forums</i>
                </form>
            </div>
            <h3>Liste des résultats</h3>
            <?php
            if(count($searchResults) < 1) {
                echo "<div>Aucun résultat</div>";
            } else {
                foreach($searchResults as $website) {
                    ?>
                    <div style="height: 90px;">
                        <div style="float: left; width: 155px;">
                            <img src="http://www.apercite.fr/api/apercite/120x90/yes/http://<?php echo $website['url']; ?>">
                        </div>
                        <div style="float: left;">
                            <h5><a href='http://<?php echo $website['url']; ?>'><?php echo utf8_encode($website['title']); ?></a></h5>
                            <p><i><?php echo $website['url']; ?></i></p>
                        </div>
                    </div>
                    <hr />
                    <?php
                }
            }
            ?>
        </div>
    </body>
</html>