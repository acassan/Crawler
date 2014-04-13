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
        if(preg_match("#www.societe.com/societe/-?(.+)-([0-9]+).html#i", $DocInfo->url, $society)) {
            echo $DocInfo->url. "\n ";
            // Handling society
            $dom    = new DOMDocument();
            @$dom->loadHTML($DocInfo->content);
            $tables = array();
            $societyData = array();
            $societyData['name'] = rtrim(ltrim(Tools::formatWord($society[1])));
            $societyData['public_name'] = rtrim(ltrim($society[1]));
            $societyData['societecom_id'] = intval($society[2]);
            $societyData['societecom_url'] = $DocInfo->url;

            $societyInformations = $dom->getElementById('synthese')->getElementsByTagName('p');
            $societyData['description'] = rtrim(ltrim($societyInformations->item(1)->nodeValue));

            // President
            preg_match("#(.+) (.+) (.+) (.+) en ([0-9]+)#i", $societyInformations->item(2)->nodeValue, $presidentTmp);
            $presidentData = array();
            $presidentData['civility']  = Tools::formatWord($presidentTmp[1]);
            $presidentData['firstname'] = Tools::formatWord($presidentTmp[2]);
            $presidentData['lastname']  = Tools::formatWord($presidentTmp[3]);
            $birthDate = new \DateTime(Tools::formatWord($presidentTmp[5]));
            $presidentData['birthdate'] = $birthDate->format('Y-m-d H:i:s');

            $presidentId = $this->createOrUpdateClient($presidentData);

            /** @var DOMElement $table */
            foreach($dom->getElementsByTagName('table') as $table) {
               if(preg_match("#font-size:11px;#i", $table->getAttribute('style')) && count($table) < 2) {
                   $tables[] = $table;
               }
            }

            foreach($tables as $table) {
                if($tables[0] instanceof DOMElement) {
                    /** @var DOMNodeList $tr */
                    $trList = $table->getElementsByTagName('tr');
                    foreach($trList as $tr) {
                        $keyColumn = ltrim(rtrim(Tools::formatWord($tr->getElementsByTagName('td')->item(0)->nodeValue)));
                        switch($keyColumn) {
                            case "nom commercial":
                                $societyData['commercial_name'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "activite":
                                $societyData['activity'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "siege social":
                                $societyData['headquarter'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "forme juridique":
                                $societyData['legaltype'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                                $societyData['legaltype'] = Tools::unhtmlentities(htmlentities($societyData['legaltype'],null,'UTF-8'));
                            break;
                            case "siret":
                                $societyData['siret'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "rcs":
                                $societyData['rcs'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "capital social":
                                $societyData['capital'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "immatriculation":
                                $dateTmp = new \DateTime(ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue)));
                                $societyData['registration'] = $dateTmp->format('Y-m-d H:i:s');
                            break;
                            case "nationalite":
                                $societyData['nationality'] = ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue));
                            break;
                            case "radiation":
                                $dateTmp = new \DateTime(ltrim(rtrim($tr->getElementsByTagName('td')->item(1)->nodeValue)));
                                $societyData['radiation'] = $dateTmp->format('Y-m-d H:i:s');
                            break;
                        }
                    }
                }
            }

            $societyId = $this->createOrUpdateSociety($societyData);

            // Add link between society and client
            $sql = sprintf("INSERT IGNORE INTO society_has_client(society_id,client_id,position) VALUES('%d','%d','%s')", $societyId, $presidentId, 'president');
            $this->db->query($sql);
        }
    }

    /**
     * @param $lastname
     * @param $firstname
     * @return null
     */
    protected function clientExist($lastname, $firstname)
    {
        $sSql = sprintf("SELECT * FROM client WHERE lastname = '%s' AND firstname = '%s'", $lastname, $firstname);
        $results = $this->db->query($sSql);
        if(!$results) {
            return null;
        }

        foreach($results as $client) {
            return $client;
        }

        return null;
    }

    /**
     * @param $clientData
     * @return mixed
     */
    protected function createOrUpdateClient($clientData)
    {
        $client = $this->clientExist($clientData['lastname'], $clientData['firstname']);

        if(is_null($client)) {
            $this->db->Insert($clientData, 'client','',false);
            return $this->db->insert_id;
        } else {
            $this->db->Update('client', $clientData, array('id' => $client['id']),'', false);
            return $client['id'];
        }
    }

    /**
     * @param $name
     * @return null
     */
    protected function societyExist($name)
    {
        $sSql = sprintf("SELECT * FROM society WHERE name = '%s'",$name);
        $results = $this->db->query($sSql);
        if(!$results) {
            return null;
        }

        foreach($results as $society) {
            return $society;
        }

        return null;
    }

    /**
     * @param $societyData
     * @return mixed
     */
    protected function createOrUpdateSociety($societyData)
    {
        $society = $this->societyExist($societyData['name']);

        if(is_null($society)) {
            $this->db->Insert($societyData, 'society','',false);
            return $this->db->insert_id;
        } else {
            $this->db->Update('society', $societyData, array('id' => $society['id']),'',false);
            return $society['id'];
        }
    }
}