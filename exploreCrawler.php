<?php

require_once 'Crawler/ExploreCrawler.php';
require_once 'Lib/Database.php';

echo "Garbage Colletor enabled : " . (gc_enabled() ? 'OUI' : 'NON') . "\n";

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

    echo number_format(memory_get_usage(), 0, '.', ','). " octets\n";

    unset($crawler);
}