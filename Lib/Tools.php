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
        $search = array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý');
        $replace = array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y');
        $word = str_replace($search,$replace,$word);
        $word = strtolower($word);

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

    protected static function rmBlacklistWords($string)
    {
        // Blacklist word
        $blackListWords = array('<?php', '?>', ';', '"');
        $replace        = array('', '', '', '');
        $string         = str_replace($blackListWords, $replace, $string);
    }
}