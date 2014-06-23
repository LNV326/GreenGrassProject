<?php

namespace Site\GalleryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class ShowControllerTest extends WebTestCase {
		
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
	
	public function testShowCategory() {
		$client = static::createClient();
		$container = $client->getContainer();
		
		$router = $container->get('router');
		$cats = $container->get('doctrine')->getManager()->getRepository('SiteGalleryBundle:ImageCategory')->getCats();
		
		//foreach($cats as $cat) {
			$path = $router->generate('site_gallery_category', array('cRefId' => 'undercover'));
			$crawler = $client->request('GET', $path);
			$names = $crawler->filter('div.module-navigate.left a')->extract(array('_text'));
			$this->assertContains('Undercover', $names);
		//}
	}
}
?>