<?php

require_once "BaseCrawler.php";
require_once "CrawlerInterface.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";
require_once "phpCrawler/PHPCrawlerURLDescriptor.class.php";

class ExploreCrawler extends BaseCrawler implements CrawlerInterface
{
    /**
     * @var array
     */
    protected $directory;

    /**
     * @var array
     */
    protected  $pages;

    /**
     * Handle the page crawled
     * @param PHPCrawlerDocumentInfo $DocInfo
     * @return bool
     */
    public function handle(PHPCrawlerDocumentInfo $DocInfo)
    {
        if(array_key_exists(md5($DocInfo->url), $this->pages)) {
            return true;
        }

        $linksFound = 0;
        $pageUrls   = array();

        foreach($DocInfo->links_found as $linkInfo) {

            // Limit research on website link
            if (preg_match('#\.(jpg|gif|png|pdf|jpeg|css|js|ico|google|youtube|api|facebook|twitter)$# i', $linkInfo['url_rebuild']) == 0) {

                $linkUrl = parse_url($linkInfo['url_rebuild']);
                $linkUrl = $linkUrl['host'];

                if(!array_key_exists($linkUrl, $pageUrls)) {
                     // Add website to verify
                    $sql = sprintf("INSERT IGNORE INTO website_to_verify VALUES('%s','%s',0,NOW())", md5($linkUrl), $linkUrl);
                    $this->db->query($sql);

                    $pageUrls[$linkUrl] = true;
                    $linksFound++;
                }
            }
        }

        // Add page to directory
        $sSql = sprintf("INSERT INTO directory_page VALUES(%d,'%s','%s', %d, NOW(), NOW())", $this->directory['id'], md5($DocInfo->url), $DocInfo->url, $linksFound);
        $this->db->query($sSql);

        echo number_format(memory_get_usage(), 0, '.', ','). " octets\n";   

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function loadDirectoryPages()
    {
        if(is_array($this->pages)) {
            return $this->pages;
        }

        if(empty($this->directory['id'])) {
            throw new \Exception('Empty directory');
        }

        $pages  = array();
        $sSql   = sprintf("SELECT page FROM directory_page WHERE directory_id = %d", $this->directory['id']);
        foreach($this->db->query($sSql) as $page) {
            $pages[$page['page']] = true;
        }

        $this->pages = $pages;

        return true;
    }

    /**
     * @param array $directory
     */
    public function initDirectory($directory)
    {
        $this->directory = $directory;
        $this->loadDirectoryPages();
    }
}