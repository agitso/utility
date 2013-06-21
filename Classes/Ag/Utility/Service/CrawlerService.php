<?php

namespace Ag\Utility\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CrawlerService {

	/**
	 * @param string $url
	 * @return \Symfony\Component\DomCrawler\Crawler
	 */
	public function getCrawlerByUri($url) {
		return new \Symfony\Component\DomCrawler\Crawler($this->getContent($url));
	}

	/**
	 * @param string $url
	 * @return string
	 */
	protected function getContent($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 Safari/537.31");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$content = curl_exec($ch);
		curl_close($ch);

		return $content;
	}
}