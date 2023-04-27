<?php

class Resources
{
    private $resources = [];
    private $template;
    public function __construct(Template $template)
    {
        $this->template = $template;
        $this->setResources();
    }

    public function get(): array
    {
        return $this->resources;
    }

    private function setResources()
    {
        $merged = [];
        foreach ($this->template->resourceFiles() as $resource) {
            $resourceArray = json_decode(file_get_contents(__DIR__ . '/resources/' . $resource . '.json'), true);
            $merged = array_merge_recursive($merged, $resourceArray);
        }
        $merged = array_merge_recursive($merged, $this->template->resources());
        $this->resources = $merged;
        $this->transformResourcesCatalogues();
        $this->transformResourcesCitations();
        $this->transformImageGroups();
        $this->transformResourcesSrc();
    }

    private function transformResourcesCatalogues(): void
    {
        $transformed = [];
        if (array_key_exists('catalogues', $this->resources)) {
            foreach ($this->resources['catalogues'] as $catalogue) {
                $transformedEmperorCoins = [];
                foreach ($catalogue['emperor_coins'] as $emperorCoin) {
                    $transformedEmperorCoins[$emperorCoin['uuid']] = $emperorCoin;
                }
                $catalogue['emperor_coins'] = $transformedEmperorCoins;
                $transformed[$catalogue['uuid']] = $catalogue;
            }
        }
        $this->resources['catalogues'] = $transformed;
    }

    private function transformResourcesCitations(): void
    {
        $transformed = [];
        if (array_key_exists('citations', $this->resources)) {
            foreach ($this->resources['citations'] as $citation) {
                $transformed[$citation['uuid']] = $citation;
            }
        }
        $this->resources['citations'] = $transformed;
    }

    private function transformImageGroups(): void
    {
        $transformed = [];
        $required = array_merge($this->resources['specimen_image_groups'], $this->resources['uploaded_image_groups']);
        if (array_key_exists('image_groups', $this->resources)) {
            foreach ($this->resources['image_groups'] as $imageGroup) {
                if (in_array($imageGroup['uuid'], $required)) {
                    $transformed[$imageGroup['uuid']] = $imageGroup;
                }
            }
        }
        $this->resources['image_groups'] = $transformed;
    }

    private function transformResourcesSrc(): void
    {
        $transformed = [];
        if (array_key_exists('src', $this->resources)) {
            foreach ($this->resources['src'] as $src) {
                $transformed[$src['uuid']] = $src['src'];
            }
        }
        $this->resources['src'] = $transformed;
    }
}
