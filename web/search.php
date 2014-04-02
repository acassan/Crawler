<?php
require_once '../Lib/Database.php';
require_once '../Lib/SearchEngine.php';
require_once '../Lib/Tools.php';

$db = Database::getInstance();

$currentPage    = isset($_GET['page']) ? intval($_GET['page']) : 1;
$debugMode      = isset($_GET['debug']) ? true : false;

$searchEngine = new SearchEngine(array(
    'resultsPerPage'    => 10,
    'currentPage'       => $currentPage,
    'debug'             => $debugMode,
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
                ?>
                <table class="table list-results">
                    <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th><img src="img/ico_jac.png" alt="Classement Jeux-alternatifs" /></th>
                    </tr>
                    </thead>
                <?php $i = 1; foreach($searchResults as $website) {
                    ?>
                    <tr>
                        <td class="website-preview">
                            <img src="http://www.apercite.fr/api/apercite/120x90/yes/<?php echo $website['url']; ?>">
                        </td>
                        <td>
                            <h5><a href='<?php echo $website['url']; ?>' onclick="_gaq.push(['_trackEvent', 'searchGames', 'clic', '<?php echo $website['url']; ?>']"><?php echo utf8_encode($website['title']); ?></a></h5>
                            <?php
                            if(!empty($website['jac_description'])) {
                                echo "<p class='website-description'>";
                                echo $website['jac_description'];
                                echo "</p>";
                            }
                            ?>
                            <p class="website-url"><?php echo $website['url']; ?></p>
                        </td>
                        <td style="text-align: center;">
                            <?php if(!is_null($website['ranking_jac']) && $website['ranking_jac'] > 0) { echo $website['ranking_jac']; } ?>
                        </td>
                    </tr>
                    <?php
                }
                echo "</table>";
            }
            ?>
        </div>

        <?php
        // DEBUG TRACE
        if($debugMode) {
            echo "<style>#content { display: none; }</style>";
            echo "<pre>";
            var_dump($searchEngine->debug);
            echo "</pre>";
        }
        ?>

        <script>
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-49631045-1']);
            _gaq.push(['_trackPageview']);
            _gaq.push(['_trackEvent', 'searchGames', 'search', '<?php echo $searchValue; ?>']);

            (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
    </body>
</html>