<?php

require_once 'Crawler/ExploreCrawler.php';
require_once 'Lib/Database.php';

$crawler = new ExploreCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 1,
));

$db = Database::getInstance();
$sSql = "SELECT * FROM directory";

foreach($db->query($sSql) as $directory) {
    $crawler->setDirectory($directory);
    $crawler->setURL($directory['url']);
    $crawler->go();
}