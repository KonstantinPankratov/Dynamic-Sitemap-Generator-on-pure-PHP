# PHP Sitemap Generator

PHP Sitemap Generator Utility will help you to automatically create/update sitemap.xml or sitemap.xml.gz

## Usage

```php
$sitemap = new SitemapGenerator\Sitemap;
$sitemap->generate();
```

###### Advanced options

```php
$options = array(
    'EnableGZip' => false,                          // Optional: default = false. Allows to enable GZip compressing of a sitemap.xml
    'UpdateRobots.txt' => true,                     // Optional: default = false.  Allows to enable an updating robots.txt after sitemap generation
    'SpecifyWebsiteURL' => 'http://custom-url.com/' // Optional: default = your website URL. You could specify your website url manually, if needed
);

$sitemap = new SitemapGenerator\Sitemap($options);
$sitemap->generate();
```