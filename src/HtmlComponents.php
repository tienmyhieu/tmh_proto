<?php

class HtmlComponents
{
    private $htmlComponents = [];
    public function __construct(Template $template)
    {
        $this->setHtmlComponents($template->html());
    }

    public function get($htmlComponent)
    {
        return array_key_exists($htmlComponent, $this->htmlComponents) ? $this->htmlComponents[$htmlComponent]: '';
    }

    private function setHtmlComponents($htmlComponents)
    {
        foreach ($htmlComponents as $htmlComponent) {
            $html = file_get_contents(__DIR__ . '/html/' . $htmlComponent . '.html');
            $this->htmlComponents[$this->scrubComponentName($htmlComponent)] = $html;
        }
    }

    private function scrubComponentName($component)
    {
        $types = ['common', 'elements', 'specimen', 'system'];
        foreach ($types as $type) {
            $component = str_replace($type . '/', '', $component);
        }
        return $component;
    }
}
