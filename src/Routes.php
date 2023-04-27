<?php

class Routes
{
    private $currentLocale;
    private $routes = [];
    public function __construct($currentLocale)
    {
        $this->currentLocale = $currentLocale;
        $this->setRoutes();
    }

    public function get($uuid): string
    {
        $transformed = [];
        foreach ($this->routes as $route) {
            $transformed[$route['template']] = $route['uuid'];
        }
        return array_key_exists($uuid, $transformed) ? $transformed[$uuid] : '';
    }

    public function getAlternateRoutes($locale, $uuid): array
    {
        $alternateRoutes = [];
        $localesDirectories = array_diff(scandir(__DIR__ . '/locales'), ['.', '..']);
        foreach ($localesDirectories as $directory) {
            if ($directory == $this->currentLocale) {
                continue;
            }
            $tmpRoutes = [];
            $routes = json_decode(file_get_contents(__DIR__ . '/locales/' . $directory . '/routes.json'), true);
            foreach ($routes as $route) {
                $tmpRoutes[$route['template']] = $route;
            }
            if (array_key_exists($uuid, $tmpRoutes)) {
                $alternateRoutes[$directory] = ['route' => $tmpRoutes[$uuid]['uuid'], 'title' => $locale];
            }
        }
        return $alternateRoutes;
    }

    public function getTemplateType($uuid)
    {
        foreach ($this->routes as $route) {
            if ($route['template'] == $uuid) {
                return $route['type'];
            }
        }
    }

    private function setRoutes()
    {
        $routes = json_decode(file_get_contents(__DIR__ . '/locales/' . $this->currentLocale . '/routes.json'), true);
        foreach ($routes as $route) {
            $this->routes[$route['uuid']] = $route;
        }
    }
}