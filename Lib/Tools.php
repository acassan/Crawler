<?php
Class Tools
{
    /**
     * @param $word
     * @return string
     */
    public static function formatWord($word)
    {
        // Format word
        $search     = array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý');
        $replace    = array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y');
        $word       = str_replace($search,$replace,$word);

        $word       = strtolower($word);

        $search     = array(',',';',':','/','?','.','!','*','$','^','&','"',"'",'(',')','{','}','[',']','|','`','#','=');
        $word       = str_replace($search,'',$word);

        return $word;
    }

    /**
     * @param $url
     * @return string
     */
    public static function parseUrl($url)
    {
        $search = array('https://','www.');
        $replace = array('','');

        $url = str_replace($search, $replace, $url);

        if(substr( $url, 0, 7 ) !== 'http://') {
            $url = 'http://'.$url;
        }

        $url = 'http://'.parse_url($url, PHP_URL_HOST);

        return $url; // return the formatted url
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function rmBlacklistWords($string)
    {
        // Blacklist word
        $blackListWords = array('<?php', '?>', ';', '"', 'echo');
        $string         = str_replace($blackListWords, '', $string);

        return $string;
    }

    /**
     * @param $chaineHtml
     * @return string
     */
    public static function unhtmlentities($chaineHtml) {
        $tmp = get_html_translation_table(HTML_ENTITIES);
        $tmp = array_flip ($tmp);
        $chaineTmp = strtr ($chaineHtml, $tmp);

        return $chaineTmp;
    }
}