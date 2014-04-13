<?php
require_once 'Crawler/SocieteComCrawler.php';
require_once 'Lib/Database.php';

$db = Database::getInstance();

$crawler = new SocieteComCrawler(array(
    'multiprocessing'           => false,
    'FollowMode'                => 3,
    'showReferer'               => false,
    'showContentReceived'       => false,
    'showPageRequested'         => true,
));
//
//$sSql           = "SELECT * FROM config WHERE name='society.dpt'";
//foreach($db->query($sSql) as $tmp) { $currentId = $tmp['value']; }
//
//for($i=$currentId; $i <= 99; $i++) {
//    echo "Handling dpt ". $i ." \n";
//    $crawler->setURL(sprintf("http://www.societe.com/liste-%02d.html", $i));
//    $crawler->go();
//
//    $db->Update('config', array('value' => $i), array('name' => 'society.dpt'));
//}

$crawler->setURL("http://www.societe.com");
$crawler->go();