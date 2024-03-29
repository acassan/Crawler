<?php

require_once "phpCrawler/PHPCrawler.class.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";
require_once "phpCrawler/Enums/PHPCrawlerUrlCacheTypes.class.php";

class BaseCrawler extends PHPCrawler
{
    /**
     * @var string
     */
    protected $lb;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->lb       = PHP_SAPI == "cli" ? "\n" : "\n";
        $this->db       = Database::getInstance();
        $this->options = array(
            'showPageRequested'     => true,
            'showReferer'           => true,
            'showContentReceived'   => true,
        );

        parent::__construct();
        $this->initCrawler($options);
    }
    /**
     * @inheritdoc
     */
    public function handleDocumentInfo( PHPCrawlerDocumentInfo $DocInfo)
    {
        // Print the URL and the HTTP-status-Code
        if($this->options['showPageRequested']) {
            echo "Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")".$this->lb;
        }

        // Print the refering URL
        if($this->options['showReferer']) {
            echo "Referer-page: ".$DocInfo->referer_url.$this->lb;
        }

        // Print if the content of the document was be recieved or not
        if($this->options['showContentReceived']) {
            if ($DocInfo->received == true)
                echo "Content received: ".$DocInfo->bytes_received." bytes".$this->lb;
            else
                echo "Content not received".$this->lb;
            }

        if(!$this->handle($DocInfo)) {
            return -1;
        }

        flush();
    }

    protected function initCrawler(array $optionsEntered)
    {
        $optionsDefaults = array(
            'multiprocessing'           => true,
            'multiprocessingNumber'     => 5,
            'receiveContentType'        => '#text/html#',
            'URLFilterRule'             => '#\.(jpg|gif|png|pdf|jpeg|css|js)$# i',
            'FollowMode'                => 1,
        );

        $options = array_merge($optionsDefaults, $optionsEntered);

        $this->addContentTypeReceiveRule($options['receiveContentType']);
        $this->addURLFilterRule($options['URLFilterRule']);
        $this->setFollowMode($options['FollowMode']);
        $this->setFollowRedirects(true);
        $this->enableCookieHandling(true);

        if(isset($options['sqlite'])) {
            $this->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);
        }

        if($options['multiprocessing']) {
            $this->goMultiProcessed($options['multiprocessingNumber']);
        }

        if(array_key_exists('showPageRequested', $options)) {
            $this->options['showPageRequested'] = $options['showPageRequested'];
        }

        if(array_key_exists('showReferer', $options)) {
            $this->options['showReferer'] = $options['showReferer'];
        }

        if(array_key_exists('showContentReceived', $options)) {
            $this->options['showContentReceived'] = $options['showContentReceived'];
        }
    }

    /**
     * @param $url
     * @return array|null
     */
    protected function findWebsite($url)
    {
        $sSql = sprintf("SELECT * FROM website WHERE url = '%s'", $url);
        $results = $this->db->query($sSql);
        if(!$results) {
            return null;
        }

        foreach($results as $website) {
            return $website;
        }

        return null;
    }

    /**
     * @param $website
     * @return bool
     * @throws Exception
     */
    public function saveWebsite($website)
    {
        if(is_null($website)) {
            throw new \Exception('Website empty when saved');
        }

        $now                    = new \DateTime();
        $websiteId              = $website['id'];
        $website['directories'] = json_encode($website['directories']);
        unset($website['id']);
        $website['updatedAt'] = $now->format('Y-m-d H:i:s');

        $this->db->Update('website', $website, array('id' => $websiteId));

        return true;
    }
}