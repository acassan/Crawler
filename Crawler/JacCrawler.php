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
            if($rankingLineColumns->length < 8) {
                continue;
            }

            $ranking = intval($rankingLineColumns->item(0)->nodeValue);

            if(is_null($ranking) || $ranking < 1) {
                continue;
            }

            // Handling JacId
            $jacIdHTml = $dom->saveXML($rankingLineColumns->item(1));
            if      (preg_match('#im/mep/accrJeu/([0-9]+).jpg#Uis', $jacIdHTml, $websiteJacId) > 0) {}
            elseif  (preg_match('#-jeu([0-9]+)_generale_1_1.html#Uis', $jacIdHTml, $websiteJacId) > 0) {}
            else { continue; }

            $jacId                  = $websiteJacId[1];
            $gameUrl                = Tools::parseUrl($this->getGameUrlFromJacId($jacId));
            $website                = $this->findOrCreateWebsite($gameUrl);
            $website['ranking_jac'] = $ranking;
            $website['jac_id']      = $ranking;


            echo sprintf("%s > %d %s", $gameUrl, $ranking, $this->lb);

            $this->saveWebsite($website);
        }

    }

    protected function handlingGame(PHPCrawlerDocumentInfo $DocInfo)
    {
        $this->iterations++;

        if($this->iterations > 200) {
            gc_collect_cycles();
            echo ">> Memory: ". number_format(memory_get_usage(), 0, '.', ','). " octets". $this->lb;
            $this->iterations = 1;
        }

        // Check game page
        if(!strstr($DocInfo->url, '_generale_1_1.html')) {
            return true;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($DocInfo->content);

        $descriptionDiv = $dom->getElementById('accColGauche');
        $divNodes = $descriptionDiv->getElementsByTagName('div');

        foreach($divNodes->item(2)->childNodes as $childNodes) {
            var_dump($childNodes->nodeValue);
        }
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

        echo sprintf("Website created: '%s' %s", $url, $this->lb);

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