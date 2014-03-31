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
        $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
        $word = strtolower($word);
        $word = strtr($word,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

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
}