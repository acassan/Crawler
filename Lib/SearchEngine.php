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

    public $debug = array();

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
    }

    /**
     * @param $searchString
     * @param array $options
     * @return array
     */
    public function search($searchString, $options = array())
    {
        $explodedSearch = explode(' ', $searchString);

        // Total website
        $sSql = "SELECT COUNT(*) AS total FROM website";
        foreach($this->db->query($sSql) as $totalDB) {
            $totalWebsites = $totalDB['total'];
        }

        foreach($explodedSearch as $word) {
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $word = strtolower($word);
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

            $sSql = sprintf("SELECT WD.website_id, WD.weight, W.url
                                FROM website_dictionary AS WD
                                INNER JOIN website AS W ON WD.website_id = W.id
                                WHERE WD.word = '%s'", $word);

            if(!empty($options['forum'])) {
                $sSql .= " AND W.forum = 0";
            }

            // Word weight
            $sSql = sprintf("SELECT * FROM dictionary WHERE word = '%s'", $word);
            $wordWeight = 1;
            foreach($this->db->query($sSql) as $wordWeightDB) {
                $wordWeight = 1 / $wordWeightDB['weight'];
            }

            foreach($this->db->query($sSql) as $websiteWord) {
                if(!array_key_exists($websiteWord['website_id'], $this->websitesWeight)) {
                    $this->websitesWeight[$websiteWord['website_id']] = 0;
                }

                if(strpos($websiteWord['url'], $word)) {
                    $websiteWord['weight'] *= 2;
                }

                $this->websitesWeight[$websiteWord['website_id']] += ($websiteWord['weight'] * $wordWeight);
            }
        }


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

        $this->debug['websitesWeight'] = $this->websitesWeight;
        $this->debug['websitesChoosen'] = $websitesChoosen;

        $sSql = sprintf("SELECT * FROM website WHERE id IN(%s)", implode(',', array_keys($websitesChoosen)));
        foreach($this->db->query($sSql) as $website) {
            $websitesDatabase[$website['id']] = $website;
        }

        foreach($websitesChoosen as $websiteId => $weight) {
            $websitesResult[] = $websitesDatabase[$websiteId];
        }

        return $websitesResult;
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
}