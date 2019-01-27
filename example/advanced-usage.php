<?php

require_once '../src/SitemapGenerator.php';

$options = array(
    'EnableGZip' => false,
    'UpdateRobots.txt' => true,
    'SpecifyWebsiteURL' => 'http://custom-url.com/'
);

$sitemap = new SitemapGenerator\Sitemap($options);
$sitemap->generate();