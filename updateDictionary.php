<?php
require_once 'Lib/Database.php';
$db = Database::getInstance();

$sSql           = "SELECT MAX(id) AS maxid FROM website";
foreach($db->query($sSql) as $tmp) { $websiteIdMax = $tmp['maxid']; }
echo "Max website ID : ". $websiteIdMax ." \n";
echo "Truncate dictionary .. \n";
$sSqlTruncate   = "TRUNCATE TABLE dictionary";
$sSql           = "SELECT * FROM website_dictionary WHERE website_id = [id]";
$dictionary     = array();

for($i=1; $i <= $websiteIdMax; $i++) {
    echo "Handling website id ". $i ." .. \n";
    foreach($db->query(str_replace("[id]", $i, $sSql)) as $websiteWord) {
        if(!array_key_exists($websiteWord['word'], $dictionary)) {
            $dictionary[$websiteWord['word']] = array();
        }

        $dictionary[$websiteWord['word']][] = intval($websiteWord['website_id']);
    }

    unset($websiteWord);
    gc_collect_cycles();
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