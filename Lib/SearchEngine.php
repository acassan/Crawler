<?php

Class SearchEngine
{
    /**
     * @var Database
     */
    protected $db;

    /**
     * @var int
     */
    protected $resultsPerPage = 10;

    /**
     * @var array
     */
    protected $websitesWeight = array();

    /**
     * DEBUG MODE
     */
    protected $debugMode    = false;
    public $debug           = array();

    /**
     * @var integer
     */
    protected $currentPage;

    /**
     * @var integer
     */
    protected $totalResult;

    /**
     * @var integer
     */
    protected $totalPage;

    /**
     * @param $options
     */
    public function __construct($options = array())
    {
        $this->db = Database::getInstance();

        if(array_key_exists('resultsPerPage', $options)) {
            $this->resultsPerPage = $options['resultsPerPage'];
        }

        if(array_key_exists('currentPage', $options)) {
            $this->currentPage = $options['currentPage'];
        }

        if(array_key_exists('debug', $options)) {
            $this->debugMode = $options['debug'];
        }
    }

    /**
     * @param $searchString
     * @param array $options
     * @return array
     */
    public function search($searchString, $options = array())
    {
        $explodedSearch = explode(' ', $searchString);
        $options        = array_merge($options, $this->prepareOptions($explodedSearch));
        $websiteWords   = array();

        $this->logDebug('params', 'searchValue', $searchString);
        $this->logDebug('params', 'currentPage', $this->getCurrentPage());
        $this->logDebug('params', 'resultsPerPage', $this->resultsPerPage);
        $this->logDebug('params', 'options', $options);


        foreach($explodedSearch as $word) {

            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $word = strtolower($word);
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

            // Word weight
            $sSql = sprintf("SELECT * FROM dictionary WHERE word = '%s'", $word);
            $wordWeight = 1;
            foreach($this->db->query($sSql) as $wordWeightDB) {
                $wordWeight = ceil(1000 / $wordWeightDB['weight']);
            }

            $this->logDebug('wordWeight', $word, $wordWeight);

            $where = $this->_handleWhereWithOptions($options);

            $sSql = sprintf("SELECT WD.website_id, WD.weight, W.url
                                FROM website_dictionary AS WD
                                INNER JOIN website AS W ON WD.website_id = W.id
                                WHERE WD.word = '%s' AND (W.game = 1 OR W.jac_id > 0) %s", $word, $where);

            foreach($this->db->query($sSql) as $websiteWord) {

                if(!array_key_exists($websiteWord['website_id'], $websiteWords)) {
                    $websiteWords[$websiteWord['website_id']] = array();
                }
                $websiteWords[$websiteWord['website_id']][] = $word;

                if(!array_key_exists($websiteWord['website_id'], $this->websitesWeight)) {
                    $this->websitesWeight[$websiteWord['website_id']] = 0;
                }

                if(strpos($websiteWord['url'], $word)) {
                    $websiteWord['weight'] *= 10;
                }

                $websiteWordWeight = $websiteWord['weight'] * $wordWeight;
                $this->websitesWeight[$websiteWord['website_id']] += $websiteWordWeight;

                $this->logDebug('websiteWordWeight', $websiteWord['url'], $word, $websiteWordWeight, true);
            }
        }

        $this->logDebug('process','WebsitesBeforeMandatory', count($this->websitesWeight));

        // Mandatory word
        if(isset($options['mandatory_word'])) { $this->handleMandatoryWord($options['mandatory_word'], $websiteWords); }

        $this->logDebug('process','WebsitesAfterMandatory', count($this->websitesWeight));

        if(count($this->websitesWeight) < 1) {
            return array();
        }


        // Stats
        $this->setTotalResult(count($this->websitesWeight));
        $this->setTotalPage(ceil($this->getTotalResult()/$this->resultsPerPage));

        if($this->getCurrentPage() > $this->getTotalPage()) {
            $this->setCurrentPage(1);
        }

        // Order websitesWeight per weight
        arsort($this->websitesWeight);

        // Return X first results of websitesWeight
        $offsetArrayslice   = ($this->getCurrentPage()-1) * $this->resultsPerPage;
        $websitesChoosen    = array_slice($this->websitesWeight, $offsetArrayslice, $this->resultsPerPage, true);
        $websitesResult     = array();
        $websitesDatabase   = array();

        $sSql = sprintf("SELECT * FROM website WHERE id IN(%s)", implode(',', array_keys($websitesChoosen)));
        foreach($this->db->query($sSql) as $website) {
            $websitesDatabase[$website['id']] = $website;
        }

        foreach($websitesChoosen as $websiteId => $weight) {
            $websitesResult[] = $websitesDatabase[$websiteId];
            $this->logDebug('websitesChoosen', $websitesDatabase[$websiteId]['url'], $weight);
        }

        return $websitesResult;
    }

    /**
     * @param array $options
     * @return string
     */
    protected function _handleWhereWithOptions(array $options)
    {
        $where = "";

        if(isset($options['jac_only'])) { $where .= " AND W.jac_id > 0 "; }
        if(!empty($options['forum'])) { $where .= " AND W.forum = 0"; }

        if(isset($options['url_contain'])) {
            foreach($options['url_contain'] as $valueToSearch)
            $where .= " AND W.url LIKE '%".$valueToSearch."%' ";
        }

        return $where;
    }

    /**
     * @param array $searchValue
     * @return array
     */
    protected function prepareOptions(array &$searchValue)
    {
        $calculateOptions = array();

        foreach($searchValue as $key => $word) {
            if(strstr($word, 'url:')) {
                $tmp = explode(':', $word);
                $urlContainValue = $tmp[1];
                if(!isset($calculateOptions['url_contain'])) { $calculateOptions['url_contain'] = array(); }
                $calculateOptions['url_contain'][] = $urlContainValue;
                unset($searchValue[$key]);
            }
        }

        foreach($searchValue as $key => $word) {
            if(preg_match("#".preg_quote("*")."(.+)".preg_quote("*")."#i", $word, $matches)) {
                $mandatoryWord = $matches[1];
                if(!isset($calculateOptions['mandatory_word'])) { $calculateOptions['mandatory_word'] = array(); }
                $calculateOptions['mandatory_word'][] = $mandatoryWord;
                $searchValue[$key] = $mandatoryWord;
            }
        }

        return $calculateOptions;
    }

    /**
     * @param array $mandatoryWords
     * @param array $websiteWords
     * @return bool
     */
    protected function handleMandatoryWord(array $mandatoryWords, array $websiteWords)
    {
        foreach($mandatoryWords as $word) {
            foreach($websiteWords as $websiteId => $words) {
                if(!in_array($word, $words)) {
                    $this->logDebug('unsetMandatoryWordWebsite', $websiteId);
                    unset($this->websitesWeight[$websiteId]);
                }
            }
        }

        return true;
    }

    /**
     * @param $word
     * @return array
     */
    protected function getWebsitesWord($word)
    {
        $websites = array();

        $sSql = sprintf("SELECT websites FROM dictionary WHERE word = '%s'",$word);
        foreach($this->db->query($sSql) as $wordFound) {
            $wordWebsites   = json_decode($wordFound['websites']);
            $websites       = array_merge($websites, $wordWebsites);
        }

        return $websites;
    }

    /**
     * @param $word
     * @param $websites
     * @return array
     */
    protected function wordWebsitesWeight($word, $websites)
    {
        if(count($websites) < 1) {
            return array();
        }
        $websitesWordWeight = array();
        $sSql = sprintf("SELECT website_id, weight FROM website_dictionary WHERE website_id IN (%s) AND word = '%s'", implode(',', $websites), $word);

        foreach($this->db->query($sSql) as $websiteWord) {
            $websitesWordWeight[$websiteWord['website_id']] = $websiteWord['weight'];
        }

        return $websitesWordWeight;
    }

    /**
     * @param int $totalPage
     */
    public function setTotalPage($totalPage)
    {
        $this->totalPage = $totalPage;
    }

    /**
     * @return int
     */
    public function getTotalPage()
    {
        return $this->totalPage;
    }

    /**
     * @param int $totalResult
     */
    public function setTotalResult($totalResult)
    {
        $this->totalResult = $totalResult;
    }

    /**
     * @return int
     */
    public function getTotalResult()
    {
        return $this->totalResult;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function logDebug($keyCat,$keyValue, $value = null, $addArray = null)
    {
        if(!$this->debugMode) {
            return false;
        }

        if(!array_key_exists($keyCat, $this->debug)) {
            $this->debug[$keyCat] = array();
        }

        if(!is_null($value)) {
            if(is_null($addArray)) {
                $this->debug[$keyCat][$keyValue] = $value;
            } else {
                $this->debug[$keyCat][$keyValue][] = $value;
            }
        } else {
            $this->debug[$keyCat][] = $keyValue;
        }

        return true;
    }
}