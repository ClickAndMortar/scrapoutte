<?php

namespace Scrapoutte\Helper;

/**
 * Class UrlHelper
 * @package Scrapoutte\Helper
 * @author Michael BOUVY <michael.bouvy@clickandmortar.fr>
 */
class UrlHelper
{
    /**
     * Calculate unique hash for given URL
     *
     * @param string $url
     *
     * @return string
     */
    public function getUrlHash($url)
    {
        return sha1($url);
    }

    /**
     * Check if 2 URLs have the same host
     *
     * @param string $url1 URL
     * @param string $url2 URL
     *
     * @return bool
     */
    public function isSameHost($url1, $url2)
    {
        $host1 = parse_url($url1, PHP_URL_HOST);
        $host2 = parse_url($url2, PHP_URL_HOST);

        return $host1 === $host2;
    }
}
