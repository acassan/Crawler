<?php
require_once 'Crawler/SocieteComCrawler.php';
require_once 'Lib/Database.php';

$crawler = new SocieteComCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 3,
    'showReferer'               => false,
    'showContentReceived'       => false,
    'showPageRequested'         => true,
));

//$crawler->setURL("http://www.societe.com");
$crawler->setPageLimit(1);
//$crawler->setURL("http://www.societe.com/societe/brasseries-kronenbourg-430371021.html");
//$crawler->go();
$crawler->setURL("http://www.societe.com/societe/riverline-535190920.html");
$crawler->go();
