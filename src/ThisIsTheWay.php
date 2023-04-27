<?php

class ThisIsTheWay
{
    private $cdn = 'http://cdn.tienmyhieu.com/';
    private $domains;
    private $html;
    private $htmlComponents;
    private $inscriptions;
    private $locales;
    private $navigation;
    private $resources;
    private $routes;
    private $template;
    private $transformedComponents;
    public function __construct(
        Domains $domains,
        HtmlComponents $htmlComponents,
        Inscriptions $inscriptions,
        Locales $locales,
        Navigation $navigation,
        Resources $resources,
        Routes $routes,
        Template $template
    ) {
        $this->domains = $domains;
        $this->htmlComponents = $htmlComponents;
        $this->inscriptions = $inscriptions;
        $this->locales = $locales;
        $this->navigation = $navigation;
        $this->resources = $resources->get();
        $this->routes = $routes;
        $this->template = $template;
        $this->transformedComponents = $this->template->components();
//        echo "<pre>";
//        $resourceKeys = array_keys($this->resources);
//        sort($resourceKeys);
//        print_r($resourceKeys);
//        print_r($this->resources['emperor_coins']);
//        print_r($this->resources['catalogues']);
//        echo "</pre>";
        $this->transformResourcesAlternateRoutes();
        $this->transformResourcesInnerHtml();
        $this->translateResourcesLocales();
        $this->transformHead();
        $this->transformResourcesImages();
        $this->transformResourcesLinks();
        $this->transformResourcesSpecimenMetadata();
        $this->transformResourcesReferences();
        $this->transformResourcesImageGroups();
        $this->transformResourcesImageGroupsHtml('specimen');
        $this->transformResourcesImageGroupsHtml('uploaded');
        $this->transformResourcesCreativeCommons();
        $this->transformResourcesPageTitle('page_title');
        $this->transformResourcesPageTitle('page_sub_title');
        $this->transformResourcesNavigation();
        $this->transformBody();
        $this->transformHtml();
//        echo "<pre>";
//        print_r($this->resources['image_groups']);
//        echo "</pre>";
    }

    public function html(): string
    {
        return $this->html;
    }

    private function addLanguage($componentLang): bool
    {
        return 0 < strlen($componentLang);
    }

    private function addId($componentId): bool
    {
        return 0 < strlen($componentId);
    }

    private function currentLanguage()
    {
        return substr($this->locales->currentLocale(), 0, 2);
    }

    private function getId($templateRoute): string
    {
        $addId = $this->addId($templateRoute['id']);
        return $addId ? ' id="tmh_' . $templateRoute['id'] . '"' : '';
    }

    private function getLanguage($templateRoute): string
    {
        $addLanguage = $this->addLanguage($templateRoute['lang']);
        $lang = '.' == $templateRoute['lang'] ? $this->currentLanguage() : $templateRoute['lang'];
        return $addLanguage ? ' lang="' . $lang . '"' : '';
    }

    private function getMetadataComponentHtml($component): string
    {
        $i = 0;
        $domain = $this->domains->get($this->locales->currentLocale());
        $componentHtml = $this->htmlComponents->get($component);
        $metadataComponentHtml = '';
        $componentHtml = $this->strReplace('dir', $domain['direction'], $componentHtml);
        foreach ($this->resources[$component] as $resourcesSpecimenMetadata) {
            $eol = $i < count($this->resources[$component]) - 1 ? PHP_EOL : '';
            $metadataComponentHtml .= $resourcesSpecimenMetadata . $eol;
            $i++;
        }
        return $this->strReplace($component, $metadataComponentHtml, $componentHtml) . PHP_EOL;
    }

    private function strReplace($search, $replace, $subject)
    {
        return str_replace("{{" . $search . "}}", $replace, $subject);
    }

    private function transformBody()
    {
        $bodyHtml = $this->htmlComponents->get('body');
        $transformed = ['body' => '', 'header' => ''];
        foreach ($this->transformedComponents as $transformedComponent) {
            if (!is_array($this->resources[$transformedComponent['html']])) {
                $transformed[$transformedComponent['target']] .= $this->resources[$transformedComponent['html']];
            }
        }
        $bodyHtml = $this->strReplace('body', $transformed['body'], $bodyHtml);
        $bodyHtml = $this->strReplace('header', $transformed['header'], $bodyHtml);
        $this->resources['body'] = $bodyHtml;
        foreach ($this->resources as $key => $resource) {
            $keep = ['head', 'body'];
            if (!in_array($key, $keep)) {
                unset($key);
            }
        }
    }

    private function transformHead()
    {
        $headHtml = $this->htmlComponents->get('head');
        $metaDescription = $this->transformInnerHtml($this->resources['meta_description']);
        $metaKeywords = $this->transformInnerHtml($this->resources['meta_keywords']);
        $title = $this->transformLocale($this->resources[$this->resources['head_title_key']], '');
        $headHtml = $this->strReplace('head.meta_description', $metaDescription, $headHtml);
        $headHtml = $this->strReplace('head.meta_keywords', $metaKeywords, $headHtml);
        $headHtml = $this->strReplace('head.cdn', $this->cdn, $headHtml);
        $headHtml = $this->strReplace('head.title', $title, $headHtml);
        $headHtml = $this->strReplace('head.lang', $this->currentLanguage(), $headHtml);
        $this->resources['head'] = $headHtml;
    }

    private function transformHtml()
    {
        $domain = $this->domains->get($this->locales->currentLocale());
        $lang = $this->currentLanguage();
        $htmlLang = $lang == 'zh' ? $this->locales->currentLocale() : $lang;
        $htmlHtml = $this->htmlComponents->get('html');
        $htmlHtml = $this->strReplace('head', $this->resources['head'], $htmlHtml);
        $htmlHtml = $this->strReplace('body', $this->resources['body'], $htmlHtml);
        $htmlHtml = $this->strReplace('dir', $domain['direction'], $htmlHtml);
        $htmlHtml = $this->strReplace('lang', $htmlLang, $htmlHtml);
        $this->html = $htmlHtml;
    }

    private function transformInnerHtml($innerHtml)
    {
        if (preg_match('/(innerHtml)(\.)(.+)/', $innerHtml, $matches)) {
            $innerHtml = $this->resources['innerHtml'][$matches[3]];
        }
        if (preg_match('/(locales)(\.)(.+)/', $innerHtml, $matches)) {
            $innerHtml = $this->locales->get($matches[3]);
        }
        if (preg_match('/(images)(\.)(.+)/', $innerHtml, $matches)) {
            if (array_key_exists($matches[3], $this->resources['images'])) {
                $innerHtml = $this->resources['images'][$matches[3]];
            }
        }
        return $innerHtml;
    }

    private function transformInscription($innerHtml, $separator)
    {
        if (preg_match('/(inscriptions)(\.)(.+)/', $innerHtml, $matches)) {
            $innerHtml = $this->inscriptions->get($matches[3]) . $separator;
        }
        return $innerHtml;
    }

    private function transformLocale($innerHtml, $separator)
    {
        if (preg_match('/(locales)(\.)(.+)/', $innerHtml, $matches)) {
            $innerHtml = $this->locales->get($matches[3]) . $separator;
        }
        return $innerHtml;
    }

    private function transformMetadata($metadata)
    {
        if (preg_match('/(innerHtml)(\.)(.+)/', $metadata, $matches)) {
            $metadata = $this->resources['innerHtml'][$matches[3]];
        }
        if (preg_match('/(links)(\.)(.+)/', $metadata, $matches)) {
            $metadata =$this->resources['links'][$matches[3]];
        }
        return $metadata;
    }

    private function transformResourcesAlternateRoutes()
    {
        $alternateRoutes = $this->routes->getAlternateRoutes(
            $this->resources['alternate_route'],
            $this->template->uuid()
        );
        $transformed = [];
        foreach ($alternateRoutes as $locale => $alternateRoute) {
            $linkHtml = $this->htmlComponents->get('a');
            $domain = $this->domains->get($locale);
            $lang = substr($locale, 0, 2);
            $hrefLang = $lang == 'zh' ? $locale : $lang;
            $hrefLang = ' hreflang="' . $hrefLang . '"';
            $linkAttributes = [
                'anchor' => '',
                'id' => '',
                'lang' => '',
                'href' => 'http://' . $domain['uuid'] . '/' . $alternateRoute['route'],
                'title' => $this->locales->getByLocaleAndUuid($locale, $alternateRoute['title']),
                'innerHtml' => $domain['native_name'],
                'hreflang' => $hrefLang,
                'dir' => ' dir="'. $domain['direction'] . '"',
                'target' => '_self',
                'rel' => ' rel="alternate"'
            ];
            foreach ($linkAttributes as $key => $value) {
                $linkHtml = $this->strReplace('route.' . $key, $value, $linkHtml);
            }
            $transformed[$locale] = $linkHtml;
        }
        $this->resources['alternate_routes'] = $transformed;
        $alternateRoutesHtml = '';
        $divHtml = $this->htmlComponents->get('alternate_routes');
        $i = 0;
        $separator = " - ";
        foreach ($this->resources['alternate_routes'] as $alternateRoute) {
            $eol = $i < count($this->resources['alternate_routes']) - 1 ? PHP_EOL : '';
            $alternateRoutesHtml .= $alternateRoute . $separator . $eol;
            $i++;
        }
        $alternateRoutesHtml = substr($alternateRoutesHtml, 0, -strlen($separator));
        $divHtml = $this->strReplace('alternate_routes', $alternateRoutesHtml, $divHtml);
        $this->resources['alternate_routes'] = $divHtml . PHP_EOL;
    }

    private function transformResourcesCreativeCommons()
    {
        $creativeCommonsHtml = $this->htmlComponents->get('creative_commons');
        foreach ($this->locales->getAll() as $key => $value) {
            if (substr($key, 0, 2) == 'cc') {
                $creativeCommonsHtml = $this->strReplace('locales.' . $key, $value, $creativeCommonsHtml);
            }
        }
        $this->resources['creative_commons'] = $creativeCommonsHtml;
    }

    private function transformResourcesImageGroups()
    {
        $transformed = [];
        $groupId = 1;
        $domain = $this->domains->get($this->locales->currentLocale());
        $templateType = $this->routes->getTemplateType($this->template->uuid());
        $showAll = $templateType != 'emperor_coin';
        foreach ($this->resources['image_groups'] as $imageGroup) {
            $show = $imageGroup['default'] == '1';
            if ($show || $showAll) {
                if ($domain['direction'] == 'rtl') {
                    $imageGroup['routes'] = array_reverse($imageGroup['routes']);
                }
                $imageGroupHtml = '';
                $componentHtml = $this->htmlComponents->get('image_group');
                $componentHtml = $this->strReplace('image_group_id', $groupId, $componentHtml);
                if ($domain['direction'] == 'ltr' && $imageGroup['minimum'] > count($imageGroup['routes']) ) {
                    $imageGroupHtml .= $this->htmlComponents->get('spacer_image');
                }
                foreach ($imageGroup['routes'] as $route) {
                    $imageGroupHtml .= $this->resources['links'][$route];
                }
                if ($domain['direction'] == 'rtl' && $imageGroup['minimum'] > count($imageGroup['routes']) ) {
                    $imageGroupHtml .= $this->htmlComponents->get('spacer_image');
                }
                $titleHtml = '';
                if ($templateType == 'emperor_coin' && 0 < strlen($imageGroup['title'])) {
                    $titleHtml = $this->htmlComponents->get('specimen_title');
                    $title = $this->transformInnerHtml($imageGroup['title']);
                    $titleHtml = $this->strReplace('specimen_title', $title, $titleHtml);
                }
                $componentHtml = $this->strReplace('image_group', $imageGroupHtml, $componentHtml);
                if ($titleHtml) {
                    $transformed[$imageGroup['uuid'] . '_t'] = $titleHtml;
                }
                $transformed[$imageGroup['uuid']] = $componentHtml;
                $groupId++;
            }
        }
        $this->resources['image_groups'] = $transformed;
    }

    private function transformResourcesImages()
    {
        if ($this->resources['specimens']) {
            $transformed = [];
            foreach ($this->resources['images'] as $image) {
                if (in_array($image['specimen'], $this->resources['specimens'])) {
                    $imgHtml = $this->htmlComponents->get('img');
                    $imgHtml = $this->strReplace('image.src', $this->transformSrc($image), $imgHtml);
                    $imgHtml = $this->strReplace('image.alt', $this->resources['emperor_coin'], $imgHtml);
                    $transformed[$image['uuid']] = $imgHtml;
                }
            }
            $this->resources['images'] = $transformed;
        }
    }

    private function transformResourcesInnerHtml()
    {
        if (array_key_exists('innerHtml', $this->resources)) {
            $transformed = [];
            foreach ($this->resources['innerHtml'] as $resourcesInnerHtml) {
                if (is_array($resourcesInnerHtml['innerHtml'])) {
                    $innerHtml = '';
                    $i = 0;
                    foreach ($resourcesInnerHtml['innerHtml'] as $innerHtmlItem) {
                        $separator = $i < count($resourcesInnerHtml) - 1 ? $resourcesInnerHtml['separator'] : '';
                        $transformedInnerHtml = $this->transformInscription($innerHtmlItem, $separator);
                        $transformedInnerHtml = $this->transformLocale($transformedInnerHtml, $separator);
                        $innerHtml .= $transformedInnerHtml;
                        $i++;
                    }
                    $transformed[$resourcesInnerHtml['uuid']] = trim($innerHtml);
                } else {
                    $transformed[$resourcesInnerHtml['uuid']] = $resourcesInnerHtml['innerHtml'];
                }
            }
            $this->resources['innerHtml'] = $transformed;
        }
    }

    private function transformResourcesLinks()
    {
        $transformed = [];
        foreach ($this->resources['links'] as $templateRoute) {
            $linkHtml = $this->htmlComponents->get('a');
            $innerHtml = $this->transformInnerHtml($templateRoute['innerHtml']);
            $route = $this->transformRoute($innerHtml, $templateRoute['href']);
            $transformedRoute = [
                'anchor' => 0 < strlen($templateRoute['anchor']) ? '#' . $templateRoute['anchor'] : '',
                'href' => $route,
                'id' => $this->getId($templateRoute),
                'innerHtml' => $innerHtml,
                'lang' => $this->getLanguage($templateRoute),
                'target' => $templateRoute['target'],
                'title' => $this->transformLocale($templateRoute['title'], ''),
                'dir' => '',
                'hreflang' => '',
                'rel' => ''
            ];
            foreach ($transformedRoute as $key => $value) {
                $linkHtml = $this->strReplace('route.' . $key, $value, $linkHtml);
            }
            $transformed[$templateRoute['uuid']] = $linkHtml;
        }
        $this->resources['links'] = $transformed;
    }

    private function translateResourcesLocales()
    {
        $possibleKeys = ['coin', 'emperor', 'emperor_coin'];
        foreach ($possibleKeys as $possibleKey) {
            if (array_key_exists($possibleKey, $this->resources)) {
                $this->resources[$possibleKey] = $this->transformLocale($this->resources[$possibleKey], '');
            }
        }
    }

    private function transformResourcesMetadata($metadata)
    {
        $domain = $this->domains->get($this->locales->currentLocale());
        $labelLang = ' lang="' . $this->currentLanguage() . '"';
        $component = $metadata['component'];
        $componentHtml = $this->htmlComponents->get($metadata['component'] . '_' . $domain['direction']);
        $label = $this->transformLocale($metadata['label'], '');
        $transformedInnerHtml = $this->transformMetadata($metadata['metadata'], '');
        $componentHtml = $this->strReplace($component . '.lang', $this->getLanguage($metadata), $componentHtml);
        $componentHtml = $this->strReplace($component . '.label', $label, $componentHtml);
        $componentHtml = $this->strReplace($component . '.label_lang', $labelLang, $componentHtml);
        return $this->strReplace($component . '.innerHtml', $transformedInnerHtml, $componentHtml);
    }


    private function transformResourcesNavigation()
    {
        $navigations = $this->navigation->get($this->resources['navigation']);
        $domain = $this->domains->get($this->locales->currentLocale());
        if ($domain['direction'] == 'rtl') {
            $navigations = array_reverse($navigations);
        }
        $transformed = [];
        $i = 0;
        $lang = ' lang="' . $this->currentLanguage() . '"';
        $separatorHtml = $this->htmlComponents->get('span');
        $separatorHtml = $this->strReplace('span.innerHtml', Navigation::SEPARATOR, $separatorHtml);
        $separatorHtml = $this->strReplace('span.lang', $lang, $separatorHtml);
        foreach ($navigations as $uuid => $navigation) {
            $linkHtml = $this->htmlComponents->get('a');
            $transformedRoute = [
                'anchor' => '',
                'href' => $navigation['route'],
                'id' => ' id="tmh_nav_' . ($i + 1) . '"',
                'innerHtml' => $navigation['innerHtml'],
                'lang' => $lang,
                'target' => '_self',
                'title' => $navigation['title'],
                'dir' => '',
                'hreflang' => '',
                'rel' => ''
            ];
            foreach ($transformedRoute as $key => $value) {
                $linkHtml = $this->strReplace('route.' . $key, $value, $linkHtml);
            }
            $transformed[$uuid] = $linkHtml;
            if ($i < count($navigations) - 1) {
                $transformed[$uuid . "_c"] = $separatorHtml;
            }
            $i++;
        }
        if (3 == count($transformed)) {
            $lastNavigation = [
                'emperor_coin' => 'coin',
                'reference' => 'reference'
            ];
            $templateType = $this->routes->getTemplateType($this->template->uuid());
            $transformed[] = $separatorHtml;
            $lastNavigationText = $this->transformLocale($this->resources[$lastNavigation[$templateType]], '');
            $spanHtml = $this->htmlComponents->get('span');
            $spanHtml = $this->strReplace('span.innerHtml', $lastNavigationText, $spanHtml);
            $spanHtml = $this->strReplace('span.lang', $lang, $spanHtml);
            $transformed[] = $spanHtml;
        }
        $this->resources['navigation'] = $transformed;
        $divHtml = $this->htmlComponents->get('navigation');
        $linksHtml = '';
        $i = 0;
        foreach ($this->resources['navigation'] as $navigation) {
            $eol = $i < count($this->resources['navigation']) - 1 ? PHP_EOL : '';
            $linksHtml .= $navigation . $eol;
            $i++;
        }
        $divHtml = $this->strReplace('navigation', $linksHtml, $divHtml);
        $this->resources['navigation'] = $divHtml . PHP_EOL;
    }

    private function transformResourcesPageTitle($type)
    {
        $typeKeyExists = array_key_exists($type . '_key', $this->resources);
        if ($typeKeyExists && array_key_exists($this->resources[$type . '_key'], $this->resources)) {
            $title = $this->transformLocale($this->resources[$this->resources[$type . '_key']], '');
            $titleLang = ' lang="' . $this->currentLanguage() . '"';
            $titleHtml = $this->htmlComponents->get($type);
            $titleHtml = $this->strReplace( $type . '.innerHtml', $title, $titleHtml);
            $titleHtml = $this->strReplace( $type . '.lang', $titleLang, $titleHtml);
            $this->resources[$type] = $titleHtml . PHP_EOL;
        }
    }

    private function transformResourcesReferences()
    {
        $transformed = [];
        foreach ($this->resources['citations'] as $uuid => $citation) {
            if (in_array($uuid, $this->resources['references'])) {
                $referenceHtml = $this->htmlComponents->get('reference');
                $link = $this->resources['links'][$uuid];
                $citationText = $this->transformLocale($citation['citation'], '');
                $citationText = $this->strReplace('title', $link, $citationText);
                $citationText = $this->strReplace('page', $citation['page'], $citationText);
                $referenceHtml = $this->strReplace('reference', $citationText, $referenceHtml);
                $transformed[$uuid] = $referenceHtml;
            }
        }
        $this->resources['citations'] = $transformed;
        $citationsHtml = '';
        $referencesHtml = $this->htmlComponents->get('references');
        $i = 0;
        foreach ($this->resources['citations'] as $citation) {
            $eol = $i < count($this->resources['citations']) - 1 ? PHP_EOL : '';
            $citationsHtml .= $citation . $eol;
            $i++;
        }
        $referencesHtml = $this->strReplace('references', $citationsHtml, $referencesHtml);
        $this->resources['references'] = $referencesHtml . PHP_EOL;
    }

    private function transformResourcesImageGroupsHtml($type)
    {
        if (array_key_exists($type . '_image_groups', $this->resources)) {
            $resourceImageGroups = $this->resources[$type . '_image_groups'];
            $i = 0;
            $componentsHtml = '';
            foreach ($resourceImageGroups as $resourceImageGroup) {
                $eol = $i < count($resourceImageGroups) - 1 ? PHP_EOL : '';
                $keyExists = array_key_exists($resourceImageGroup, $this->resources['image_groups']);
                $titleKeyExists = array_key_exists($resourceImageGroup . '_t', $this->resources['image_groups']);
                if ($titleKeyExists) {
                    $componentsHtml .= $this->resources['image_groups'][$resourceImageGroup. '_t'] . $eol;
                }
                if ($keyExists) {
                    $componentsHtml .= $this->resources['image_groups'][$resourceImageGroup] . $eol;
                }
                $i++;
            }
            $componentsHtml .= PHP_EOL;
            $imageGroupsHtml = $this->htmlComponents->get($type . '_images') . PHP_EOL;
            $imageGroupsHtml = $this->strReplace( $type . '_images', trim($componentsHtml), $imageGroupsHtml);
            $this->resources[$type . '_image_groups'] = $imageGroupsHtml;
        }
    }

    private function transformResourcesSpecimenMetadata()
    {
        if (array_key_exists('specimen_metadata', $this->resources)) {
            $transformed = [];
            foreach ($this->resources['specimen_metadata'] as $resourcesSpecimenMetadata) {
                $componentHtml = $this->transformResourcesMetadata($resourcesSpecimenMetadata);
                $transformed[$resourcesSpecimenMetadata['uuid']] = "\t" . $componentHtml;
            }
            $this->resources['specimen_metadata'] = $transformed;
            $this->resources['specimen_metadata'] = $this->getMetadataComponentHtml('specimen_metadata');
        }
    }

    private function transformRoute($innerHtml, $route)
    {
        if (preg_match('/(src)(\.)(.+)/', $route, $matches)) {
            $imageType = 'images';
            if (strpos($innerHtml, 'uploads')) {
                $imageType = 'uploads';
            }
            $route = $this->cdn . $imageType . '/1024/' . $this->resources['src'][$matches[3]];
        }
        if (preg_match('/(routes)(\.)(.+)/', $route, $matches)) {
            $route = $this->routes->get($matches[3]);
        }
        return $route;
    }

    private function transformSrc($image)
    {
        $src = $image['src'];
        if (preg_match('/(src)(\.)(.+)/', $image['src'], $matches)) {
            $src = $this->cdn . $image['type'] . '/128/' . $this->resources['src'][$matches[3]];
        }
        return $src;
    }
}
