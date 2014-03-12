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
     * @param $options
     */
    public function __construct($options = array())
    {
        $this->db = Database::getInstance();

        if(array_key_exists('resultsPerPage', $options)) {
            $this->resultsPerPage = $options['resultsPerPage'];
        }
    }

    /**
     * @param $searchString
     * @return array
     */
    public function search($searchString)
    {
        $explodedSearch = explode(' ', $searchString);

        foreach($explodedSearch as $word) {
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $word = strtolower($word);
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

            $websites           = $this->getWebsitesWord($word);
            $wordWebsitesWeight = $this->wordWebsitesWeight($word, $websites);

            foreach($wordWebsitesWeight as $websiteId => $weight) {
                if(!array_key_exists($websiteId, $this->websitesWeight)) {
                    $this->websitesWeight[$websiteId] = 0;
                }

                $this->websitesWeight[$websiteId] += $weight;
            }
        }

        if(count($this->websitesWeight) < 1) {
            return array();
        }

        // Order websitesWeight per weight
        arsort($this->websitesWeight);

        // Return X first results of websitesWeight
        $websitesChoosen    = array_slice($this->websitesWeight, 0, $this->resultsPerPage, true);
        $websitesResult     = array();
        $websitesDatabase   = array();

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
}