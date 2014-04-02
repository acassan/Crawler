<?php
require_once 'Lib/Database.php';
$db = Database::getInstance();

$sSqlTruncate   = "TRUNCATE TABLE dictionary";
$sSql           = "SELECT * FROM website_dictionary";

$dictionary     = array();

foreach($db->query($sSql) as $websiteWord) {
    if(!array_key_exists($websiteWord['word'], $dictionary)) {
        $dictionary[$websiteWord['word']] = array();
    }

    $dictionary[$websiteWord['word']][] = intval($websiteWord['website_id']);
}

foreach($dictionary as $word => $websites) {
    $dictionaryWord = array(
        'word'      => $word,
        'websites'  => json_encode($websites),
        'weight'    => count($websites),
        'updatedAt' => date('Y-m-d H:i:s'),
    );

    $db->Insert($dictionaryWord, 'dictionary');
}

echo count($dictionary)." mots inseres dans le dictionaire";