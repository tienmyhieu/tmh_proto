<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once (__DIR__ . '/Domains.php');
require_once (__DIR__ . '/HtmlComponents.php');
require_once (__DIR__ . '/Inscriptions.php');
require_once (__DIR__ . '/Locales.php');
require_once (__DIR__ . '/Navigation.php');
require_once (__DIR__ . '/Reference.php');
require_once (__DIR__ . '/Resources.php');
require_once (__DIR__ . '/Routes.php');
require_once (__DIR__ . '/Template.php');
require_once (__DIR__ . '/ThisIsTheWay.php');
//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";
if (!defined('LOCALE')) {
    define('LOCALE', 'vi-VN');
}
$currentLocale = LOCALE ?: "vi-VN";
$sitemaps = strpos($_SERVER['REQUEST_URI'], 'sitemaps');
if ($sitemaps) {
    require_once (__DIR__ . '/sitemap.php');
}
// 0qqgudfb
// d5yvbpay
$templateId = 'yq3sniql';
$domains = new Domains();
$routes = new Routes($currentLocale);
$template = new Template($routes, $templateId);
$htmlComponents = new HtmlComponents($template);
$inscriptions = new Inscriptions();
$locales = new Locales($currentLocale, $template);
$resources = new Resources($template);
$navigation = new Navigation($locales, $routes);
$thisIsTheWay = new ThisIsTheWay(
    $domains,
    $htmlComponents,
    $inscriptions,
    $locales,
    $navigation,
    $resources,
    $routes,
    $template
);
echo $thisIsTheWay->html();
