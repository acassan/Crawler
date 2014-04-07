<?php
require_once 'Crawler/ExploreCrawler.php';
require_once 'Lib/Database.php';

$db     = Database::getInstance();
$now    = new \DateTime();
$sSql   = sprintf("SELECT * FROM directory WHERE updatedAt <= '%s'", date('Y-m-d H:i:s',strtotime('-2 weeks')));

foreach($db->query($sSql) as $directory) {
    $crawler = new ExploreCrawler(array(
        'multiprocessing'           => false,
        'FollowMode'                => 1,
        'showPageRequested'         => false,
        'showReferer'               => false,
        'showContentReceived'       => false,
        'sqlite'                    => true,
    ));
    $crawler->initDirectory($directory);
    $crawler->setURL($directory['url']);
    $crawler->go();

    $sSql = sprintf("UPDATE directory SET updatedAt = '%s', crawler_id = ''  WHERE id = %d", $now->format('Y-m-d H:i:s'), $directory['id']);
    $db->query($sSql);

    unset($crawler);
}