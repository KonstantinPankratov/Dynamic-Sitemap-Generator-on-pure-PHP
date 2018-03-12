<?php

	//GETTING URLs AND THEIR PROCESSING
	$website = "http://EXAMPLE.com/";

	$html = file_get_contents($website);
	$dom = new DOMDocument;

	@$dom->loadHTML($html);

	$links = $dom->getElementsByTagName('a');

	$array_with_completed_urls = [];

	foreach ($links as $link)
	{
		$url = $link->getAttribute('href');

		if( strpos($url, "http") === FALSE AND strpos($url, "{") === FALSE AND strpos($url, "#") === FALSE AND strpos($url, "javascript") === FALSE AND strpos($url, "mailto") === FALSE AND $url != "/" AND $url != ""){
	    	array_push($array_with_completed_urls, $url);
	    }
	}

	$array_with_completed_urls = array_unique($array_with_completed_urls);


	// XML SITEMAP GENERATION
	$time = date('c', time()); // getting datetime in google sitemap format

	$XML = new SimpleXMLElement('<urlset/>');
	$XML->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
	$XML->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
	$XML->addAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9');

	$url = $XML->addChild('url');
	$url->addChild('loc', $website);
	$url->addChild('lastmod', $time);
	$url->addChild('priority', '1.00');
  
	foreach ($array_with_completed_urls as $completed_url)
	{
		$url = $XML->addChild('url');
		$url->addChild('loc', $completed_url);
		$url->addChild('lastmod', $time);
		$url->addChild('priority', '0.80');
	}

	$complete_xml = $XML->asXML();

	$FILEsitemapXML = fopen("sitemap.xml", "w");
	fwrite($FILEsitemapXML, $complete_xml);
	fclose($FILEsitemapXML);
	
	//CRON JOBS
	$CRON = "cron-settings.txt";
	shell_exec("crontab" . $CRON);
?>