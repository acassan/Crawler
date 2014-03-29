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
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>SnapGameSearch</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="base.css">
        <link rel="stylesheet" href="search.css">
    </head>
    <body>
        <header>
            <form action="search.php" method="GET">
            <div class="floatLeft"><img src="img/ico-pad.png" /></div>
            <div class="floatLeft">
                <input class="searchValue" type="text" name="search" style="width: 400px;" value="<?php echo $searchValue; ?>" />
            </div>
            <div class="floatLeft">
                <input class="btn-search" type="image" src="img/ico-search.png" style="width: 30px; height: 37px;" />
            </div>
            </form>
            <div class="floatRight statistics">
                Total sites: <?php echo $stats['websites']; ?> <br />
                Sites jeux: <?php echo $stats['webgames']; ?> <br />
                Forums: <?php echo $stats['forums']; ?>
            </div>
        </header>
        <div id="content">

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