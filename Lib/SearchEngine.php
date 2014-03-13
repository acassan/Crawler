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
     * @param array $options
     * @return array
     */
    public function search($searchString, $options = array())
    {
        $explodedSearch = explode(' ', $searchString);

        foreach($explodedSearch as $word) {
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
            $word = strtolower($word);
            $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

            $sSql = sprintf("SELECT WD.website_id, WD.weight
                                FROM website_dictionary AS WD
                                INNER JOIN website AS W ON WD.website_id = W.id
                                WHERE WD.word = '%s'", $word);

            if(!empty($options['forum'])) {
                $sSql .= " AND W.forum = 1";
            }

            foreach($this->db->query($sSql) as $websiteWord) {
                if(!array_key_exists($websiteWord['website_id'], $this->websitesWeight)) {
                    $this->websitesWeight[$websiteWord['website_id']] = 0;
                }

                $this->websitesWeight[$websiteWord['website_id']] += $websiteWord['weight'];
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
}