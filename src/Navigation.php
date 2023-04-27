<?php

class Navigation
{
    const SEPARATOR = " &raquo; ";
    private $navigation = [];
    private $navigationNodes = [];
    public function __construct(Locales $locales, Routes $routes)
    {
        $this->setNavigation($locales, $routes);
    }

    public function get($uuid): array
    {
        $keyExists = array_key_exists($uuid, $this->navigation);
        $navigation = $keyExists ? $this->getNavigationNode($this->navigation[$uuid]) : [];
        $this->setNavigationNodes($navigation['uuid'], $navigation);
        $this->scrubNavigationNodes();
        return $this->navigationNodes;
    }

    private function setNavigationNodes($uuid, $node)
    {
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $this->setNavigationNodes($value['uuid'], $value);
            }
        }
        $this->navigationNodes[$uuid] = $node;
    }

    private function scrubNavigationNodes()
    {
        $transformed = [];
        foreach ($this->navigationNodes as $key => $node) {
            unset($node['ancestor']);
            unset($node['uuid']);
            $transformed[$key] = $node;
        }
        $this->navigationNodes = $transformed;
    }

    private function getNavigationNode($node)
    {
        if ($node['ancestor']) {
            $node['ancestor'] = $this->getNavigationNode($this->navigation[$node['ancestor']]);
        }
        return $node;
    }

    private function setNavigation(Locales $locales, Routes $routes)
    {
        $navigations = json_decode(file_get_contents(__DIR__ . '/resources/navigation.json'), true);
        foreach ($navigations as $navigation) {
            if (preg_match('/(routes)(\.)(.+)/', $navigation['route'], $matches)) {
                $navigation['route'] = $routes->get($matches[3]);
            }
            if (preg_match('/(locales)(\.)(.+)/', $navigation['title'], $matches)) {
                $navigation['title'] = $locales->get($matches[3]);
            }
            if (preg_match('/(locales)(\.)(.+)/', $navigation['innerHtml'], $matches)) {
                $navigation['innerHtml'] = $locales->get($matches[3]);
            }
            $this->navigation[$navigation['uuid']] = $navigation;
        }
    }
}