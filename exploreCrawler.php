<?php

require_once 'Crawler/ExploreCrawler.php';
require_once 'Lib/Database.php';



$db     = Database::getInstance();
$sSql   = "SELECT * FROM directory";
die("ok");

foreach($db->query($sSql) as $directory) {
    $crawler = new ExploreCrawler(array(
        'multiprocessing'           => false,
        'FollowMode'                => 1,
    ));
    $crawler->setDirectory($directory);
    $crawler->setURL($directory['url']);
    $crawler->go();
}