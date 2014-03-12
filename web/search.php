<?php
require_once '../Lib/Database.php';
require_once '../Lib/SearchEngine.php';

$searchEngine = new SearchEngine(array(
    'resultsPerPage'    => 20,
));

$searchValue    = empty($_GET['search']) ? "Jeux stratégie joueur" : $_GET['search'];
$searchResults  = $searchEngine->search($searchValue);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <title>Moteur de recherche</title>
    </head>
    <body>
        <div style="position: relative; width: 500px; height: 300px; border: 1px black dotted; margin: auto auto;">
            <div>
                <form action="search.php" method="GET">
                    <input type="text" name="search" style="width: 400px;" value="<?php echo $searchValue; ?>" /> &nbsp; <input type="submit" value="Rechercher" />
                </form>
            </div>
            <?php
            if(count($searchResults) < 1) {
                echo "<div>Aucun résultat</div>";
            } else {
                foreach($searchResults as $website) {
                    echo "<div><a href='".$website['url']."'>".$website['title']."</a></div>";
                }
            }
            ?>
        </div>
    </body>
</html>