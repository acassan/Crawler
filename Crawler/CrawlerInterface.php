<?php
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";

interface CrawlerInterface
{
    /**
     * Handle crawler
     * @param PHPCrawlerDocumentInfo $DocInfo
     * @return mixed
     */
    public function handle( PHPCrawlerDocumentInfo $DocInfo );
}