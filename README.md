# PHP Sitemap Generator

#### What does it can?

* Create sitemap.xml or sitemap.xml.gz
* Update sitemap.xml or sitemap.xml.gz
* Update robots.txt

## Usage

```php
$sitemap = new SitemapGenerator\Sitemap;
$sitemap->generate();
```

###### Advanced options

```php
/**
 * array['EnableGZip']        boolean 
 * array['UpdateRobots.txt']  boolean 
 * array['RobotsDir']         string 
 * array['SitemapDir']        string 
 * array['SpecifyWebsiteURL'] string 
 */

$options = array(
    'EnableGZip'        => false,                   // Optional: default = false. Allows to enable GZip compressing of a sitemap.xml
    'UpdateRobots.txt'  => true,                    // Optional: default = false.  Allows to enable an updating robots.txt after sitemap generation
    'RobotsDir'         => '/',                     // Optional: default = '/' (root dir). You could specify your path to robots.txt manually
    'SitemapDir'        => '/sitemap/',             // Optional: default = '/' (root dir). You could specify your path to sitemap.xml manually
    'SpecifyWebsiteURL' => 'http://custom-url.com/' // Optional: default = your website URL. You could specify your website's url manually
);

$sitemap = new SitemapGenerator\Sitemap($options);
$sitemap->generate();
```