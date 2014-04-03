<?php

require_once "BaseCrawler.php";
require_once "CrawlerInterface.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";
require_once "Lib/Tools.php";

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
            if(strstr($title,'301') == false && strstr($title,'302') == false && strstr($title,'moved') == false && strstr($title,'sorry,') == false) {
                $this->website['title'] = Tools::formatWord(utf8_decode($title));

                // Check directory website
                if($this->isDirectory($DocInfo->url) || $this->isDirectory($this->website['title'])) {
                    $this->website['directory'] = 1;
                }
            }
        }


        $bodyContentNode = $dom->getElementsByTagName('body');
//        if($bodyContentNode->length == 0 ) { return; }

        $bodyContent    = strip_tags($bodyContentNode->item(0)->nodeValue);
        $words          = explode(' ', $bodyContent);
        $wordsCount     = 0;

        foreach($words as $word) {
            if(strlen($word) > 3) {
                $word = Tools::formatWord($word);

                if(preg_match('/[^a-zA-Z0-9]/', $word)) {
                  continue;
                }

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

        $this->website = $this->findWebsite($url);

        if(is_null($this->website)) {
            $this->website = array(
                'url'           => $url,
                'directories'   => json_encode(array()),
                'createdAt'     => date('Y-m-d H:i:s'),
                'updatedAt'     => date('Y-m-d H:i:s'),
            );

            $this->db->Insert($this->website, 'website');
            $this->website['id'] = intval($this->db->insert_id);
        } else {
            unset($this->website['title']);
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
     */
    public function updateDictionaries()
    {
        // Delete website dictionary
        $sSql = "DELETE FROM website_dictionary WHERE website_id = ". intval($this->website['id']);
        $this->db->query($sSql);

        foreach($this->websiteDictionary as $word => $weight) {
            if($this->website['id'] > 0 && !empty($word)) {
                $now    = new \DateTime();
                $fields = array(
                    'website_id'    => $this->website['id'],
                    'word'          => $word,
                    'weight'        => $weight,
                );

                $this->db->Insert($fields, 'website_dictionary');
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
     * @param $content
     * @return bool
     */
    protected function isDirectory($content)
    {
        if (stripos($content, 'annuaire')) {
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
        // Remove spacing
        $title = ltrim($title);
        $title = rtrim($title);

        // Remove accents
        $search = array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý');
        $replace = array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y');
        $title = str_replace($search,$replace,$title);

        return $title;
    }

    /**
     * @param $directoryId
     * @return bool
     */
    public function setWebsiteDirectory($directoryId)
    {
        $currentDirectories = json_decode($this->website['directories']);
        if(!is_array($currentDirectories)) {
            $currentDirectories = array();
        }

        if(!is_array($this->website['directories'])) {
            $this->website['directories'] = array();
        }

        if(!array_key_exists($directoryId, $currentDirectories)) {
            $this->website['directories'][] = $directoryId;
        }

        return true;
    }

    /**
     * Alias
     * @return bool
     */
    public function saveWebsite()
    {
        return parent::saveWebsite($this->website);
    }
}