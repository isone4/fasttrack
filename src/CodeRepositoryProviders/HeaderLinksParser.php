<?php

namespace App\CodeRepositoryProviders;

class HeaderLinksParser
{
    private readonly array $explodedLinks;

    /**
     * @param array $header
     */
    public function __construct(array $header)
    {
        $this->explodedLinks = explode(",", ($header['link']['0']) ?? "");
    }

    public function headerLinks(): array
    {
        $headerlinks = [];
        foreach ($this->explodedLinks as $explodedlink) {
            $explodedlink = trim($explodedlink);
            $beginning = strpos($explodedlink, '<') + 1;
            $end = strpos($explodedlink, '>') - 1;
            $url = substr($explodedlink, $beginning, $end);
            $linktype = strpos($explodedlink, 'rel=') + 5;
            $type = substr($explodedlink, $linktype, -1);
            $headerlinks[$type] = $url;
        }

        return $headerlinks;
    }
}