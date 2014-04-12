<?php
require_once 'Crawler/SocieteComCrawler.php';
require_once 'Lib/Database.php';

$crawler = new SocieteComCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 3,
    'showReferer'               => false,
    'showContentReceived'       => false,
));
$crawler->setPageLimit(1);

$crawler->setURL("http://www.societe.com");
$crawler->go();

