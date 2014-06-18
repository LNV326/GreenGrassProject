<?php

namespace Site\GalleryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ShowControllerTest extends WebTestCase {
	
	var $CATS = array('Rivals',
			'Porsche',
			'Most Wanted (CG)',
			'The Run');
	
	public function testIndex() {
		$client = static::createClient();
		$container = $client->getContainer();
		$cats = $container->get('doctrine')->getManager()->getRepository('SiteGalleryBundle:ImageCategory')->getCats();
		
		$crawler = $client->request('GET', '/gallery/');	
		$arr = $crawler->filter('div.gallery_thumb_name')->extract(array('_text'));
		foreach($cats as $elm) {
			$this->assertContains($elm->getName(), $arr);
		}
// 		$this->assertSame($arr, $this->CATS);
		//$this->assertArrayHasKey("Porsche"+$arr[1], $arr);
		//$this->assertTrue($crawler->filter('html:contains("Porsche1")')->count() > 0);
	}
	
	
}
?>