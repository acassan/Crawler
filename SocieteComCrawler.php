<?php
require_once 'Crawler/SocieteComCrawler.php';
require_once 'Lib/Database.php';

$crawler = new SocieteComCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 3,
    'showReferer'               => false,
    'showContentReceived'       => false,
    'showPageRequested'         => false,
));

$crawler->setURL("http://www.societe.com");
//$crawler->setURL("http://www.societe.com/societe/riverline-535190920.html");
$crawler->go();

