<?php

    namespace SitemapGeneration;

    class Sitemap
    {

        private $website;

        protected $html;
        protected $dom;

        function __construct()
        {
            $this->website = $this->getWebsiteURL();

            $this->html = file_get_contents($this->website);

            $this->dom = new \DOMDocument;
            $this->dom->loadHTML($this->html);
        }

        public function generate() {
            $this->createXML();
        }

        public function setWebsiteURL($url) {
            $this->website = $url;
        }

        protected function getWebsiteURL() {
            return sprintf(
                "%s://%s/",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                $_SERVER['SERVER_NAME']
            );
        }

        protected function getURLs() {

            $array_with_completed_urls = [];
            $links = $this->dom->getElementsByTagName('a');

            foreach ($links as $link)
            {
                $url = $link->getAttribute('href');

                if( strpos($url, "http")       === FALSE AND
                    strpos($url, "{")          === FALSE AND
                    strpos($url, "#")          === FALSE AND
                    strpos($url, "javascript") === FALSE AND
                    strpos($url, "mailto")     === FALSE AND
                    $url != "/" AND
                    $url != "")
                {
                    array_push($array_with_completed_urls, $url);
                }
            }

            return array_unique($array_with_completed_urls);
        }

        protected function createXML()
        {
            $time = date('c', time());

            $XML = new \SimpleXMLElement('<urlset/>');

            $XML->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $XML->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $XML->addAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9');

            $url = $XML->addChild('url');
            $url->addChild('loc', $this->website);
            $url->addChild('lastmod', $time);
            $url->addChild('priority', '1.00');

            $urls = $this->getURLs();

            foreach ($urls as $completed_url)
            {
                $url = $XML->addChild('url');
                $url->addChild('loc', $completed_url);
                $url->addChild('lastmod', $time);
                $url->addChild('priority', '0.80');
            }

            $complete_xml = $XML->saveXML();

            $this->writeFile("sitemap.xml", $complete_xml);

        }

        private function writeFile($filename, $content)
        {
            $file = fopen($filename, "w");
            fwrite($file, $content);
            fclose($file);
        }

        public function runCRON()
        {
            $CRON = file_get_contents(".cronconfig");
            //shell_exec("crontab" . $CRON);
        }
    }

    $sitemap = new Sitemap;
    $sitemap->generate();

?>