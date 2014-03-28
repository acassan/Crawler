<?php

require_once "BaseCrawler.php";
require_once "CrawlerInterface.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";

class IndexCrawler extends BaseCrawler implements CrawlerInterface
{
    /**
     * @var array
     */
    protected $website;

    /**
     * @var array
     */
    protected $dictionary = array();

    /**
     * @var array
     */
    protected $websiteDictionary = array();

    /**
     * @inheritdoc
     */
    protected function processUrl(PHPCrawlerURLDescriptor $UrlDescriptor)
    {
        if (preg_match('#\.(jpg|gif|png|pdf|jpeg|css|js|ico|google|youtube|api|facebook|twitter)# i', $UrlDescriptor->url_rebuild) == 0) {
            return parent::processUrl($UrlDescriptor);
        }

        return false;
    }

    /**
     * Index the website
     * @param PHPCrawlerDocumentInfo $DocInfo
     * @return bool
     */
    public function handle(PHPCrawlerDocumentInfo $DocInfo)
    {
        // Check game website
        if($this->isGame($DocInfo->content)) {
            $this->website['game'] = 1;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($DocInfo->content);

        if(empty($this->website['title'])) {
            $titleNode = $dom->getElementsByTagName('title');
            $title = $titleNode->length > 0 ? ($dom->getElementsByTagName('title')->item(0)->nodeValue) : $this->website['url'];
            $title = $this->formatTitle($title);
            $this->website['title'] = utf8_decode($title);
        }

        $bodyContentNode = $dom->getElementsByTagName('body');
        if($bodyContentNode->length == 0 ) { return; }

        $bodyContent    = strip_tags($bodyContentNode->item(0)->nodeValue);
        $words          = explode(' ', $bodyContent);
        $wordsCount     = 0;

        foreach($words as $word) {
            if(strlen($word) > 3) {

                // Format word
                $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
                $word = strtolower($word);
                $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

                if(preg_match('/[^a-zA-Z0-9]/', $word)) {
                  continue;
                }

                // Dictionary
                if(!array_key_exists($word, $this->dictionary)) {
                    $this->dictionary[$word] = array();
                }

                $this->dictionary[$word][] = intval($this->website['id']);

                // Website dictionary
                if(!array_key_exists($word, $this->websiteDictionary)) {
                    $this->websiteDictionary[$word] = 0;
                }
                $this->websiteDictionary[$word]++;

                $wordsCount++;
            }
        }

        return true;
    }

    /**
     * @param $url
     * @return array|bool
     */
    public function initWebsite($url)
    {
        if(is_array($this->website)) {
            return $this->website;
        }

        $sSql = sprintf("SELECT * FROM website WHERE url = '%s'", $url);
        foreach($this->db->query($sSql) as $website) {
            unset($website['title']);
            $this->website = $website;
        }

        if(!is_array($this->website)) {
            $this->website = array(
                'url'       => $url,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            );

            $this->db->Insert($this->website, 'website');
            $this->website['id'] = intval($this->db->insert_id);
        }

        // Check forum website
        if ($this->isForum($url)) {
            $this->website['forum'] = 1;
        }

        return true;
    }

    /**
     * @param array $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Reset website handling
     */
    public function resetWebsite()
    {
        $this->dictionary           = array();
        $this->websiteDictionary    = array();
        $this->website              = null;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function saveWebsite()
    {
        if(is_null($this->website)) {
            throw new \Exception('Website empty when saved');
        }

        $now                = new \DateTime();
        $website            = $this->website;
        unset($website['id']);
        $website['updatedAt'] = $now->format('Y-m-d H:i:s');

        $this->db->Update('website', $website, array('id' => $this->website['id']));

        return true;
    }

    public function updateDictionaries()
    {
        // Dictionary
        $currentDictionary = array();
        if(count($this->dictionary) > 0) {
            $sSql = sprintf("SELECT * FROM dictionary WHERE word IN('%s')", implode("','", array_keys($this->dictionary)));
            foreach($this->db->query($sSql) as $word) {
                $currentDictionary[$word['word']] = $word;
            }
        }

        foreach($this->dictionary as $word => $websites) {
            if(array_key_exists($word, $currentDictionary)) {
                // Existing word
                $currentWord        = $currentDictionary[$word];
                $currentWebsites    = array_unique(array_merge(json_decode($currentWord['websites']), $websites));

                $now                = new \DateTime();
                $fieldsUpdated = array(
                    'weight'        => count($currentWebsites),
                    'websites'      => json_encode($currentWebsites),
                    'updatedAt'     => $now->format('Y-m-d H:i:s'),
                );

                $this->db->Update('dictionary', $fieldsUpdated, array('word' => $currentWord['word']));
            } else {
                // New word
                $now                = new \DateTime();
                $currentWebsites = array_unique($websites);
                $fields = array(
                    'word'          => $word,
                    'weight'        => count($currentWebsites),
                    'websites'      => json_encode($currentWebsites),
                    'updatedAt'     => $now->format('Y-m-d H:i:s'),
                );

                $this->db->Insert($fields, 'dictionary');
            }
        }

        // Website Dictionary
        $currentDictionary = array();
        if(count($this->websiteDictionary) > 0) {
            $sSql = sprintf("SELECT * FROM website_dictionary WHERE website_id = %s AND word IN('%s')", $this->website['id'], implode("','", array_keys($this->websiteDictionary)));
            foreach($this->db->query($sSql) as $word) {
                $currentDictionary[$word['word']] = $word;
            }
        }

        foreach($this->websiteDictionary as $word => $weight) {
            if(array_key_exists($word, $currentDictionary)) {
                // Existing word
                $currentWord        = $currentDictionary[$word];

                $now                = new \DateTime();
                $fieldsUpdated = array(
                    'weight'        => $weight,
                );

                $this->db->Update('website_dictionary', $fieldsUpdated, array('website_id' => $currentWord['website_id']));
            } else {
                // New word
                $now                = new \DateTime();
                $fields = array(
                    'website_id'    => $this->website['id'],
                    'word'          => $word,
                    'weight'        => $weight,
                );

                if($this->website['id'] > 0 && !empty($word)) {
                    $this->db->Insert($fields, 'website_dictionary');
                }
            }
        }

        return true;
    }

    /**
     * @param $content
     * @return bool
     */
    protected function isGame($content)
    {
        if (stripos($content, 'jeu')) {
            return true;
        }

        if (stripos($content, 'game')) {
            return true;
        }

        return false;
    }

    /**
     * @param $url
     * @return bool
     */
    protected function isForum($url)
    {
        if(stripos($url, 'forum')) {
            return true;
        }

        return false;
    }

    /**
     * @param $title
     * @return string
     */
    protected function formatTitle($title)
    {
        $title = ltrim($title);
        $title = rtrim($title);

        return $title;
    }
}