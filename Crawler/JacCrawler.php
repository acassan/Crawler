<?php

require_once "BaseCrawler.php";
require_once "CrawlerInterface.php";
require_once "phpCrawler/PHPCrawlerDocumentInfo.class.php";
require_once "Lib/Tools.php";

class JacCrawler extends BaseCrawler implements CrawlerInterface
{
    const HANDLING_RANKING  = 1;
    const HANDLING_GAME     = 2;

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
        switch($this->handlingMode) {
            case self::HANDLING_RANKING:
                $this->handlingRanking($DocInfo);
                break;
            case self::HANDLING_GAME:
                $this->handlingGame($DocInfo);
                break;
            default:
                throw new \Exception('Incorrect handling mode '. $this->handlingMode);
        }

        return true;
    }

    /**
     * @param PHPCrawlerDocumentInfo $DocInfo
     */
    protected function handlingRanking(PHPCrawlerDocumentInfo $DocInfo)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($DocInfo->content);

        $divRanking = $dom->getElementById('bcTable');
        foreach($divRanking->getElementsByTagName('tr') as $rankingLine) {
            // Init
            $ranking    = null;
            $jacId      = null;
            $gameUrl    = null;

            $rankingLineColumns = $rankingLine->getElementsByTagName('td');
            if($rankingLineColumns->length != 8) {
                continue;
            }

            var_dump($rankingLineColumns->item(0)->nodeValue);die();

            preg_match("#<span id='chifClass(.+)'>([0-9]+)</span>#Uis", $rankingLineColumns->item(0)->nodeValue, $rankingTmp);

            if(is_null($ranking) || $ranking < 1) {
                continue;
            }

            // Handling JacId
            preg_match('#im/mep/accrJeu/([0-9]+).jpg#Uis', $rankingLineColumns->item(1)->nodeValue, $websiteJacId);
            $jacId                  = $websiteJacId[1];
            $gameUrl                = Tools::parseUrl($this->getGameUrlFromJacId($jacId));
            $website                = $this->findOrCreateWebsite($gameUrl);
            $website['ranking_jac'] = $ranking;
            $website['jac_id']      = $ranking;


//            echo sprintf("%s > %d %s", $gameUrl, $ranking, $this->lb);

//            $this->saveWebsite($website);
        }

    }

    protected function handlingGame(PHPCrawlerDocumentInfo $DocInfo)
    {

    }

    /**
     * @param $jacId
     * @return mixed
     */
    protected function getGameUrlFromJacId($jacId)
    {
        $ch = curl_init('http://www.jeux-alternatifs.com/sortie.php?j='.$jacId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        preg_match('#window.location = "(.+)";#Uis', $output, $url);

        return $url[1];
    }

    protected function findOrCreateWebsite($url)
    {
        // Find website
        $website = $this->findWebsite($url);
        if(!is_null($website)) {
            return $website;
        }

        // Create website
        $website = array(
                'url'           => $url,
                'directories'   => json_encode(array()),
                'createdAt'     => date('Y-m-d H:i:s'),
                'updatedAt'     => date('Y-m-d H:i:s'),
            );

        $this->db->Insert($website, 'website');
        $website = intval($this->db->insert_id);

        // Add website to verify
        $sql = sprintf("INSERT IGNORE INTO website_to_verify VALUES('%s','%s',0,NOW())", md5($url), $url);
        $this->db->query($sql);

        return $website;
    }

    /**
     * @param int $handlingMode
     */
    public function setHandlingMode($handlingMode)
    {
        $this->handlingMode = $handlingMode;
    }
}