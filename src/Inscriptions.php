<?php

class Inscriptions
{
    private $inscriptions = [];
    public function __construct()
    {
        $this->setInscriptions();
    }

    public function get($uuid): string
    {
        return array_key_exists($uuid, $this->inscriptions) ? $this->inscriptions[$uuid]: '';
    }

    private function setInscriptions()
    {
        $inscriptions = json_decode(file_get_contents(__DIR__ . '/resources/inscriptions.json'), true);
        foreach ($inscriptions as $inscription) {
            $this->inscriptions[$inscription['uuid']] = $inscription['inscription'];
        }
    }
}