<?php
header("Content-type: text/xml");
$firstDot = strpos($_SERVER['HTTP_HOST'], '.');
$subDomain = substr($_SERVER['HTTP_HOST'], 0, $firstDot);
switch ($subDomain) {
    case 'en':
        $locale = 'en-GB';
        break;
    case 'fr':
        $locale = 'fr-FR';
        break;
    case 'ja':
        $locale = 'ja-JP';
        break;
    case 'zh-cn':
        $locale = 'zh-CN';
        break;
    case 'zh-hk':
        $locale = 'zh-HK';
        break;
    case 'zh-tw':
        $locale = 'zh-TW';
        break;
    default:
        $locale = 'vi-VN';
}
echo file_get_contents(__DIR__ . '/sitemaps/' . $locale . '.xml');

//$now = new \DateTime();
//$lastModified = $now->format('Y-m-d\\TH:i:sO');
//$protocol = 'http';
//
//$allDomains = [];
//$allRoutes = [];
//
//$domains = json_decode(file_get_contents(__DIR__ . '/resources/domains.json'), true);
//foreach ($domains as $domain) {
//    if ($domain['default'] == '1') {
//        $allDomains[$domain['locale']] = $protocol . '://' . $domain['uuid'] . '/';
//    }
//}
//ksort($allDomains);
//foreach ($allDomains as $locale => $directory) {
//    $transformed = [];
//    $routes = json_decode(file_get_contents(__DIR__ . '/locales/' . $locale . '/routes.json'), true);
//    foreach ($routes as $route) {
//        $transformed[$route['template']] = $route['uuid'];
//    }
//    $allRoutes[$locale] = $transformed;
//}
//$defaultRoutes = $allRoutes[LOCALE];
//$urlSets = [];
//foreach ($defaultRoutes as $uuid => $defaultRoute) {
//    $urlSet = ['loc' => $allDomains[LOCALE] . $defaultRoute, 'lastMod' => $lastModified, 'xhtml:link' => []];
//    foreach ($allDomains as $locale => $domain) {
//        $urlSet['xhtml:link'][$locale] = $domain . $allRoutes[$locale][$uuid];
//    }
//    $urlSets[] = $urlSet;
//}
//$urlSetNamespace = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
//$xmlNamespace = 'xmlns:xhtml="http://www.w3.org/1999/xhtml"';
/*$xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;*/
//$xmlOutput .= '<urlset ' . $urlSetNamespace . ' ' . $xmlNamespace . '>' . PHP_EOL;
//foreach ($urlSets as $urlSet) {
//    $xmlOutput .= "\t" . '<url>' . PHP_EOL;
//    $xmlOutput .= "\t\t" . '<loc>' . $urlSet['loc'] . '</loc>' . PHP_EOL;
//    $xmlOutput .= "\t\t" . '<lastmod>' . $urlSet['lastMod'] . '</lastmod>' . PHP_EOL;
//    foreach ($urlSet['xhtml:link'] as $locale => $xhtmlLink) {
//        $xmlOutput .= "\t\t" . "<xhtml:link rel='alternate' hreflang='" . $locale . "' href='" . $xhtmlLink . "' />" . PHP_EOL;
//    }
//    $xmlOutput .= "\t" . '</url>' . PHP_EOL;
//}
//$xmlOutput .= '</urlset>';
//file_put_contents(__DIR__ . '/' . LOCALE . '/vi-VN.xml', $xmlOutput);
