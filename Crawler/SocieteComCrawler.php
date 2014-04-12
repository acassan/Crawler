<?php

require_once "BaseCrawler.php";
require_once "CrawlerInterface.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";
require_once "Lib/Tools.php";

class SocieteComCrawler extends BaseCrawler implements CrawlerInterface
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
        $this->explore($DocInfo);

        return true;
    }

    protected function explore(PHPCrawlerDocumentInfo $DocInfo)
    {
        if(preg_match("#www.societe.com/societe/(.+).html#i", $DocInfo->url, $society)) {
            // Handling society
            $dom = new DOMDocument();
            @$dom->loadHTML($DocInfo->content);

            /** @var DOMElement $table */
            foreach($dom->getElementsByTagName('table') as $table) {
                echo "+table \n ";
               if(preg_match('#font-size:11px; margin-left:15px;" width="100%"#i', $table->getAttribute('style'))) {
                   echo "+tableOk \n ";
                   $tr = $table->getElementsByTagName('tr');
                   $commercialName = $tr->item(0)->getElementsByTagName('td')->item(1)->nodeValue;
                   var_dump($commercialName);
               }
            }
        }
    }
}