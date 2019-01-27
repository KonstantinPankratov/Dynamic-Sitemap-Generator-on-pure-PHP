<?php

namespace SitemapGenerator;

class Sitemap
{
    private $website;
    private $domain;
    private $scheme;

    protected $html;
    protected $dom;

    protected $maxURLs = 3000;
    protected $depth   = 0;
    protected $robots  = "robots.txt";

    protected $EnableGZip   = false;
    protected $UpdateRobots = false;
    protected $RobotsDir    = '/';
    protected $SitemapDir   = '/';

    protected $handledLinks = array();
    protected $XMLcreated   = false;

    function __construct($options = array())
    {
        if (is_array($options))
        {
            $this->EnableGZip = is_bool($options['EnableGZip']) ? $options['EnableGZip'] : false;
            $this->UpdateRobots = is_bool($options['UpdateRobots.txt']) ? $options['UpdateRobots.txt'] : false;
            $this->RobotsDir = ($options['RobotsDir'] != '') ? $options['RobotsDir'] : $this->RobotsDir;
            $this->SitemapDir = ($options['SitemapDir'] != '') ? $options['SitemapDir'] : $this->SitemapDir;

            if (filter_var($options['SpecifyWebsiteURL'], FILTER_VALIDATE_URL)) {
                $this->website = $options['SpecifyWebsiteURL'];
            } else {
                $this->website = $this->getWebsiteURL();
            }
        } else {
            $this->website = $this->getWebsiteURL();
        }

        $this->domain = parse_url($this->website)['host'];
        $this->scheme = parse_url($this->website)['scheme'];
    }

    public function generate()
    {
        try {
            $this->recursiveSearch($this->website);
            $this->createXML();

            if ($this->UpdateRobots)
                $this->updateRobotsTXT();

        } catch (\Exception $e) {
            print($e->getMessage());
        }
    }

    protected function getWebsiteURL() {
        return sprintf(
            "%s://%s/",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        );
    }

    public function enableGZip()
    {
        $this->EnableGZip = true;
    }

    protected function recursiveSearch($link)
    {
        if(strlen($link) <= 1)
            return;

        $request = $this->cURL($link);

        if ($request['errno'] != 0 || $request['http_code'] != 200)
            return;

        $html = $request['content'];

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $links = $dom->getElementsByTagName('a');

        $this->depth++;

        foreach ($links as $link) {

            if (count($this->handledLinks) >= $this->maxURLs)
                return;

            $href = $link->getAttribute('href');

            if($links[0] == '/' || $links[0] == '?')
                $href = $this->scheme . "://" . $this->domain . '/' .  $href;

            if(strlen($href) <= 1)
                return;

            $urlComponents = parse_url($href);

            if ($urlComponents === false)
                continue;

            if ($urlComponents['host'] != $this->domain)
                continue;

            if (!in_array($href, $this->handledLinks))
            {
                $this->handledLinks[] = $href;
                $this->recursiveSearch($href);
            }
        }

        $this->depth--;

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

        $urls = $this->handledLinks;

        foreach ($urls as $link)
        {
            $linkComponent = parse_url($link);
            $pathDepth =  array_filter(explode('/', $linkComponent['path']));

            switch (count($pathDepth))
            {
                case 0:
                case 1:
                    $priority = 0.80;
                    break;
                case 2:
                    $priority = 0.70;
                    break;
                case 3:
                    $priority = 0.60;
                    break;
                default:
                    $priority = 0.50;
                    break;
            }

            $url = $XML->addChild('url');
            $url->addChild('loc', $link);
            $url->addChild('lastmod', $time);
            $url->addChild('priority', $priority);
        }

        $complete_xml = $XML->saveXML();

        if (!file_exists($this->SitemapDir)) {
            if (!mkdir($this->SitemapDir, 0777, true)) {
                throw new \Exception('Cannot create ' . $this->SitemapDir . ' directory.');
            }
        }

        if ($this->EnableGZip) {
            $this->writeGZip($this->SitemapDir . "sitemap.xml.gz", $complete_xml);
        } else {
            $this->writeFile($this->SitemapDir . "sitemap.xml", $complete_xml);
        }

        $this->XMLcreated = true;

    }

    private function updateRobotsTXT()
    {
        $original_name  = $this->RobotsDir . $this->robots;
        $duplicate_name = $this->RobotsDir . 'test-'.$this->robots;

        if (!file_exists($original_name))
            throw new \Exception('Cannot update ' . $original_name . '. File does not exists.');

        $duplicate = fopen($duplicate_name, 'w');
        $original  = fopen($original_name, 'r');

        $once = true;

        while (($line = fgets($original)) !== false)
        {
            if (strpos($line, 'sitemap'))
            {
                if (!$once) continue;

                $dir = $this->scheme . '://' . $this->domain . $this->SitemapDir;

                if ($this->EnableGZip) {
                    fwrite($duplicate, 'Sitemap: ' . $dir . 'sitemap.xml.gz');
                } else {
                    fwrite($duplicate, 'Sitemap: ' . $dir . 'sitemap.xml');
                }

                fwrite($duplicate, PHP_EOL);
                $once = false;
                continue;
            }

            fwrite($duplicate, $line);
        }

        fclose($duplicate);
        fclose($original);

        unlink($original_name);
        rename($duplicate_name, $original_name);
    }

    private function writeFile($filename, $content)
    {
        $file = fopen($filename, "w");
        fwrite($file, $content);
        fclose($file);
    }

    private function writeGZip($filename, $content) {
        $file = gzopen($filename, 'w');
        gzwrite($file, $content);
        return gzclose($file);
    }

    public function runCRON()
    {
        //$CRON = file_get_contents(".cronconfig");
        //shell_exec("crontab" . $CRON);
    }

    private function cURL($link)
    {
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(
            CURLOPT_CUSTOMREQUEST  => "GET",        // Request Type
            CURLOPT_POST           => false,        // Post
            CURLOPT_USERAGENT      => $user_agent,  // User agent
            CURLOPT_RETURNTRANSFER => true,         // Return web page
            CURLOPT_HEADER         => false,        // Return headers
            CURLOPT_FOLLOWLOCATION => true,         // Follow redirects
            CURLOPT_ENCODING       => "",           // Encodings
            CURLOPT_AUTOREFERER    => true,         // Referer
            CURLOPT_CONNECTTIMEOUT => 120,          // Connect timeout
            CURLOPT_TIMEOUT        => 120,          // Response timeout
            CURLOPT_MAXREDIRS      => 10,           // Stop on 10 redirects
        );

        $ch = curl_init( $link );
        curl_setopt_array( $ch, $options );

        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );

        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;

        return $header;
    }
}
?>