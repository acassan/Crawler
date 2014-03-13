<?php

require_once 'Crawler/ExploreCrawler.php';
require_once 'Lib/Database.php';

$db     = Database::getInstance();
$sSql   = "SELECT * FROM directory";

foreach($db->query($sSql) as $directory) {
    $crawler = new ExploreCrawler(array(
        'multiprocessing'           => false,
        'FollowMode'                => 1,
        'showPageRequested'         => false,
        'showReferer'               => false,
        'showContentReceived'       => false,
    ));
    $crawler->initDirectory($directory);
    $crawler->setURL($directory['url']);
    $crawler->go();

    unset($crawler);
}