<?php

class Domains
{
    private $domains = [];
    public function __construct()
    {
        $this->setDomains();
    }

    public function get($locale): array
    {
        foreach ($this->domains as $uuid => $domain) {
            if ($domain['locale'] == $locale && $domain['default'] == '1') {
                return $domain;
            }
        }
        return $this->domains['tienmyhieu.com'];
    }

    private function setDomains()
    {
        $domains = json_decode(file_get_contents(__DIR__ . '/resources/domains.json'), true);
        foreach ($domains as $domain) {
            $this->domains[$domain['uuid']] = $domain;
        }
    }
}