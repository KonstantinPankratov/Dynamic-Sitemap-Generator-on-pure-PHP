<?php

require_once '../src/SitemapGenerator.php';

$options = array(
    'EnableGZip'        => false,
    'UpdateRobots.txt'  => true,
    'RobotsDir'         => '/',
    'SitemapDir'        => '/sitemap/',
    'SpecifyWebsiteURL' => 'http://custom-url.com/'
);

$sitemap = new SitemapGenerator\Sitemap($options);
$sitemap->generate();