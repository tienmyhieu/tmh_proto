<?php

class Template
{
    private $template;
    private $uuid;
    public function __construct(Routes $routes, $uuid)
    {
        $this->uuid = $uuid;
        $this->setTemplate($routes->getTemplateType($uuid));
    }

    public function components()
    {
        return $this->template['components'];
    }

    public function html()
    {
        return $this->template['html'];
    }

    public function locales()
    {
        return $this->template['locales'];
    }

    public function referenceId()
    {
        return $this->template['resources']['reference_id'] ?? '';
    }

    public function resourceFiles()
    {
        return $this->template['resource_files'];
    }

    public function resources()
    {
        return $this->template['resources'];
    }

    public function uuid()
    {
        return $this->uuid;
    }

    private function setTemplate($type)
    {
        $htmlTemplate = json_decode(file_get_contents(__DIR__ . '/resources/html.json'), true);
        $systemTemplate = json_decode(file_get_contents(__DIR__ . '/template/system.json'), true);
        $template = json_decode(file_get_contents(__DIR__ . '/template/' . $type . '/'. $this->uuid . '.json'), true);
        $typeTemplate = json_decode(file_get_contents(__DIR__ . '/template/' . $type . '.json'), true);
        $template = array_merge_recursive($template, $systemTemplate, $typeTemplate);
        $template['components'] = $this->transformComponents($template);
        $template['html'] = $this->transformHtml($htmlTemplate, $template);
        sort($template['locales']);
        $this->template = $template;
    }

    private function transformComponents($template): array
    {
        $components = [];
        foreach ($template['components'] as $component) {
            $components[$component['order']] = ['html' => $component['html'], 'target' => $component['target']];
        }
        ksort($components);
        return $components;
    }

    private function transformHtml($htmlTemplate, $template): array
    {
        $html = [];
        foreach ($template['html'] as $templateHtml) {
            $templates = [];
            $templateGroup = $htmlTemplate[$templateHtml];
            foreach ($templateGroup as $template) {
                $templates[] = $templateHtml . '/' . $template;
            }
            $html = array_merge($html, $templates);
        }
        sort($html);
        return $html;
    }
}
