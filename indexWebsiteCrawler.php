<?php
require_once 'Crawler/IndexCrawler.php';
require_once 'Lib/Database.php';
require_once 'Lib/Tools.php';
$db = Database::getInstance();

// Init Crawler
$urlToCheck = Tools::parseUrl($argv[1]);
var_dump($urlToCheck);
$sSql = sprintf("SELECT * FROM website_to_verify WHERE url ='%s'", $urlToCheck);
foreach($db->query($sSql) as $website) {

    $crawler = new IndexCrawler(array(
        'multiprocessing'           => false,
        'FollowMode'                => 0,
        'showReferer'               => false,
        'showContentReceived'       => false,
    ));
    $crawler->setPageLimit(10);
    $crawler->setFollowRedirectsTillContent(true);
    $crawler->resetWebsite();
    $crawler->initWebsite($website['url']);
    $crawler->setURL($website['url']);
    $crawler->setWebsiteDirectory($website['directory']);
    $crawler->go();
    $crawler->saveWebsite();
    $crawler->updateDictionaries();

    // Update website to verified
    $db->Update('website_to_verify',array('verified' => 1, 'updatedAt' => date('Y-m-d H:i:s')), array('id' => $website['id']));

    unset($crawler);
    gc_collect_cycles();
    echo ">> Memory: ". number_format(memory_get_usage(), 0, '.', ','). " octets \n";
}

