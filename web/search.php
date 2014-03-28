<?php
require_once '../Lib/Database.php';
require_once '../Lib/SearchEngine.php';
require_once '../Lib/Tools.php';

$db = Database::getInstance();

$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

$searchEngine = new SearchEngine(array(
    'resultsPerPage'    => 10,
    'currentPage'       => $currentPage,
));

$searchValue        = empty($_GET['search']) ? "Jeux stratégie joueur" : $_GET['search'];
$searchValueEngine  = $db->escape_string(Tools::formatWord(utf8_decode($searchValue)));

$searchOptions  = array(
    'forum' => empty($_GET['forum']) ? 0 : 1,
);
$searchResults  = $searchEngine->search($searchValueEngine, $searchOptions);

// STATS
$stats              = array();
$sSql               = "SELECT COUNT(*) AS number FROM website";
$statTmp            = $db->query($sSql)->fetch_assoc();
$stats['websites']  = $statTmp['number'];
$sSql               = "SELECT COUNT(*) AS number FROM website WHERE game = 1";
$statTmp            = $db->query($sSql)->fetch_assoc();
$stats['webgames']  = $statTmp['number'];
$sSql               = "SELECT COUNT(*) AS number FROM website WHERE forum = 1";
$statTmp            = $db->query($sSql)->fetch_assoc();
$stats['forums']    = $statTmp['number']
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <link rel="shortcut icon" href="favicon.ico">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <link rel="stylesheet" type="text/css" media="screen" href="search.css" />
        <title>Moteur de recherche</title>
    </head>
    <body>
        <div id="stats">
            Sites: <span class="stats-websites"><?php echo $stats['websites']; ?></span><br />
            Jeux: <span class="stats-webgames"><?php echo $stats['webgames']; ?></span><br />
            Forum: <span class="stats-forums"><?php echo $stats['forums']; ?></span>
        </div>
        <div id="content" style="position: relative;">
            <div>
                <form action="search.php" method="GET">
                    <input type="text" name="search" style="width: 400px;" value="<?php echo $searchValue; ?>" /> &nbsp; <input type="submit" value="Rechercher" /> <br />
                    <input type="checkbox" name="forum" value="<?php echo $_GET['forum']; ?>" /> <i>Exclure les forums</i>
                </form>
            </div>
            <h3>Liste des résultats</h3>

            <h6><?php for($i=1; $i <= $searchEngine->getTotalPage(); $i++) { echo "[<a href='?search=".$searchValue."&page=$i'>$i</a>]"; } ?></h6>
            <?php
            if(count($searchResults) < 1) {
                echo "<div>Aucun résultat</div>";
            } else {
                foreach($searchResults as $website) {
                    ?>
                    <div style="height: 90px;">
                        <div style="float: left; width: 155px;">
                            <img src="http://www.apercite.fr/api/apercite/120x90/yes/<?php echo $website['url']; ?>">
                        </div>
                        <div style="float: left;">
                            <h5><a href='<?php echo $website['url']; ?>'><?php echo utf8_encode($website['title']); ?></a></h5>
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