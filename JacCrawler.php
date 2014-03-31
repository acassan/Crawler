<?php
require_once 'Crawler/JacCrawler.php';
require_once 'Lib/Database.php';

$crawler = new JacCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 3,
    'showReferer'               => false,
    'showContentReceived'       => false,
));
$crawler->setPageLimit(1);

$crawler->setHandlingMode(JacCrawler::HANDLING_RANKING);
$crawler->setURL("http://www.jeux-alternatifs.com/index.php?p=jeuHitP");
$crawler->go();

