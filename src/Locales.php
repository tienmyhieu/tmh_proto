<?php

class Locales
{
    private $currentLocale;
    private $locales = [];
    private $localesFiles = [];
    public function __construct($currentLocale, Template $template)
    {
        $this->currentLocale = $currentLocale;
        $this->localesFiles = $template->locales();
        $this->setLocales();
    }

    public function currentLocale()
    {
        return $this->currentLocale;
    }

    public function get($uuid): string
    {
        return array_key_exists($uuid, $this->locales) ? $this->locales[$uuid]: '';
    }

    public function getAll(): array
    {
        return $this->locales;
    }

    public function getByLocaleAndUuid($locale, $uuid)
    {
        $locales = $this->getLocalesByLocale($locale);
        return array_key_exists($uuid, $locales) ? $locales[$uuid]: '';
    }

    private function setLocales()
    {
        $this->locales = $this->getLocalesByLocale($this->currentLocale);
    }

    private function getLocalesByLocale($locale): array
    {
        $merged = [];
        foreach ($this->localesFiles as $localesFile) {
            $localesArray = json_decode(
                file_get_contents(__DIR__ . '/locales/' . $locale . '/' . $localesFile . '.json'),
                true
            );
            $merged = array_merge($merged, $localesArray);
        }
        ksort($merged);
        return $merged;
    }
}
