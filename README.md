# PHP Sitemap Generator

PHP Sitemap Generator Utility will help you to automatically create/update sitemap.xml or sitemap.xml.gz

##Usage

```php
$sitemap = new SitemapGenerator\Sitemap;
$sitemap->generate();
```

######Advanced options

```php
$options = array(
    'EnableGZip' => false,
    'UpdateRobots.txt' => true,
    'SpecifyWebsiteURL' => 'http://custom-url.com/'
);

$sitemap = new SitemapGenerator\Sitemap;
$sitemap->generate();
```