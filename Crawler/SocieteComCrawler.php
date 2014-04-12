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
            $dom    = new DOMDocument();
            @$dom->loadHTML($DocInfo->content);
            $tables = array();

            /** @var DOMElement $table */
            foreach($dom->getElementsByTagName('table') as $table) {
               if(preg_match("#font-size:11px;#i", $table->getAttribute('style')) && count($table) < 3) {
                   $tables[] = $table;
               }
            }

            $commercialName      = ltrim(rtrim($tables[0]->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->nodeValue));
            $activity            = ltrim(rtrim($tables[0]->getElementsByTagName('tr')->item(1)->getElementsByTagName('td')->item(1)->nodeValue));
            $category            = ltrim(rtrim($tables[0]->getElementsByTagName('tr')->item(2)->getElementsByTagName('td')->item(1)->nodeValue));
            $headQuarter         = ltrim(rtrim($tables[0]->getElementsByTagName('tr')->item(3)->getElementsByTagName('td')->item(1)->nodeValue));
            $legalForm           = ltrim(rtrim($tables[0]->getElementsByTagName('tr')->item(4)->getElementsByTagName('td')->item(1)->nodeValue));
            var_dump($commercialName,$activity,$category,$headQuarter, $legalForm);
        }
    }
}