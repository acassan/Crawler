<?php
require_once 'Crawler/IndexCrawler.php';
require_once 'Lib/Database.php';
$db = Database::getInstance();

// Init Crawler
$crawler = new IndexCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 3,
    'showReferer'               => false,
    'showContentReceived'       => false,
));

$sSql = "SELECT * FROM website_to_verify WHERE verified = 0 LIMIT 0,3";
foreach($db->query($sSql) as $website) {
    $crawler->resetWebsite();
    $crawler->setWebsiteToVerify($website);
    $crawler->setURL($website['url']);
    $crawler->go();
    $crawler->saveWebsite();
    $crawler->updateDictionaries();

    // Update website to verified
    $db->Update('website_to_verify',array('verified' => 1, 'updatedAt' => date('Y-m-d H:i:s')), array('id' => $website['id']));
}

