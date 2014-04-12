<?php

require_once "BaseCrawler.php";
require_once "CrawlerInterface.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";
require_once "Lib/Tools.php";

class SocieteCrawler extends BaseCrawler implements CrawlerInterface
{

    /**
     * @var integer
     */
    protected $iterations;

    /**
     * @var integer
     */
    protected $handlingMode;

    /**
     * @param PHPCrawlerDocumentInfo $DocInfo
     * @return bool|mixed
     * @throws Exception
     */
    public function handle(PHPCrawlerDocumentInfo $DocInfo)
    {
        $this->Explore($DocInfo);

        return true;
    }

    protected function explode(PHPCrawlerDocumentInfo $DocInfo)
    {
        if(preg_match("#www.societe.com/societe/(+.).html#i", $DocInfo->url, $society)) {
            var_dump($society[1]);
        }
    }
}