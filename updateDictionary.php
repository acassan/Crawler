<?php
require_once 'Lib/Database.php';
$db = Database::getInstance();

// Select max ID
$sSql           = "SELECT MAX(id) AS maxid FROM website";
foreach($db->query($sSql) as $tmp) { $websiteIdMax = $tmp['maxid']; }
echo "Max website ID : ". $websiteIdMax ." \n";

// Select current ID
$sSql           = "SELECT * FROM config WHERE name='dictionary.websiteIdUpdated'";
foreach($db->query($sSql) as $tmp) { $currentId = $tmp['value']; }

if($currentId >= $websiteIdMax) {
    die("Dictionary ok");
}

if($currentId == 0) {
    echo "Truncate dictionary .. \n";
    $sSqlTruncate   = "TRUNCATE TABLE dictionary";
    $db->query($sSqlTruncate);
}


for($i = 1; $i <= 150; $i++) {
    // Handling website dictionary
    $currentWebsiteId = $currentId + $i;
    echo "Handling website id ". $currentWebsiteId ." \n";
    $sSql           = "SELECT * FROM website_dictionary WHERE website_id = ". $currentWebsiteId;
    foreach($db->query($sSql) as $websiteWord) {
        $word = $websiteWord['word'];
        $sSql = "SELECT * FROM dictionary WHERE word = '".$word."'";
        $dictionaryWord = "";
        foreach($db->query($sSql) as $dictionaryWordBase) { $dictionaryWord = $dictionaryWordBase; }

        if(!is_array($dictionaryWord)) {
            // Create word
            $dictionaryWord = array(
                'word'      => $word,
                'websites'  => json_encode(array(intval($currentWebsiteId))),
                'weight'    => $websiteWord['weight'],
                'updatedAt' => date('Y-m-d H:i:s'),
            );

            $db->Insert($dictionaryWord, 'dictionary');

        } else {
            // Update word
            $websiteWord['weight']      += $websiteWord['weight'];
            $websiteWord['websites']    = json_decode($websiteWord['websites']);
            $websiteWord['websites'][]  = intval($currentWebsiteId);
            $websiteWord['websites']    = array_unique($websiteWord['websites']);
            $websiteWord['updatedAt']   = date('Y-m-d H:i:s');

            $db->Update('dictionary', $websiteWord,array('word' => $websiteWord['word']),array('word'));
        }

        $db->Update('config', array('value' => $currentWebsiteId), array('name' => 'dictionary.websiteIdUpdated'));
    }

    gc_collect_cycles();
}