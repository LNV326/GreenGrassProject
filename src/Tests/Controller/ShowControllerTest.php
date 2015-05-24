<?php

namespace Site\GalleryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Site\GalleryBundle\Entity\ImageCategory;

class ShowControllerTest extends WebTestCase {
	
	private $client;
	private $container;
	
	/**
	 * Инициируем эмулятор клиентской части
	 */
	private function initGlobals() {
		$this->client = static::createClient();
		$this->container = $this->client->getContainer();
	}
		
	/**
	 * Тест основной страницы на то, что содержит все категории 
	 */ 
	public function testIndex() {
		// Получаем страницу
		$this->initGlobals();
		$crawler = $this->client->request('GET', '/gallery/');	
		// Проверяем статус-код
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		// Получаем список категорий, который будем искать на странице
		$cats = $this->container->get('doctrine')->getManager()->getRepository('SiteGalleryBundle:ImageCategory')->getCats();
		$arr = $crawler->filter('div.gallery_thumb_name')->extract(array('_text'));	
		// Проверяем наличие всех категорий на странице
		foreach($cats as $elm) {
			$this->assertContains($elm->getName(), $arr);
		}
// 		$this->assertSame($arr, $this->CATS);
		//$this->assertArrayHasKey("Porsche"+$arr[1], $arr);
		//$this->assertTrue($crawler->filter('html:contains("Porsche1")')->count() > 0);
	}
	
	/**
	 * Тест отображения категорий
	 * @dataProvider getCategories
	 */
	public function testShowCategory( $refID, $name ) {
		$this->initGlobals();
		$router = $this->container->get('router');
		$path = $router->generate('site_gallery_category', array('cRefId' => $refID ));
		$crawler = $this->client->request('GET', $path);
		// Проверяем статус-код
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$names = $crawler->filter('div.module-navigate.left a')->extract(array('_text'));
		$this->assertContains($name, $names);
	}
	
	/**
	/* 
	 * @dataProvider getAlbumsProvider
	 */
	protected function createClient(array $options = array(), array $server = array()) {
		// TODO: Auto-generated method stub

	}

	public function testShowAlbum( $cRefId, $aRefId, $name ) {
		$this->initGlobals();
		$router = $this->container->get('router');
		$path = $router->generate('site_gallery_album', array('cRefId' => $cRefId, 'aRefId' => $aRefId ));
		$crawler = $this->client->request('GET', $path);
		// Проверяем статус-код
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$names = $crawler->filter('div.module-navigate.left a')->extract(array('_text'));
		$this->assertContains($name, $names);
	}
	
	//==================================
	// Data Providers below
	//==================================
	
	/**
	 * Источник данных для теста категорий
	 * @return array of array of tested items
	 */
	public function getCategories() {
		$this->initGlobals();
		$cats = $this->container->get('doctrine')->getManager()->getRepository('SiteGalleryBundle:ImageCategory')->getCats();
		$return = array();
		foreach($cats as $cat) {
			$return[] = array( $cat->getRefId(), $cat->getName() );
		}
		return $return;
	}
	
	/**
	 * @depends testGetCategoryList
	 */
	public function getAlbumsProvider( $categoryList ) {
		$this->initGlobals();
		$return = array();
		foreach ($categoryList as $cat) {
			$albums = $this->container->get('doctrine')->getManager()->getRepository('SiteGalleryBundle:ImageAlbum')->getAlbums($cat);		
			foreach ($albums as $album) {
				$return[] = array( $album->getRefId(), $album->getName() );
			}
		}
		return $return;
	}
	
	public function testGetCategoryList() {
		$this->initService();
		$categoryList = $this->galleryService->getCategoryList();
		$this->assertNotNull( $categoryList );
		return $categoryList;
	}
	
	private $galleryService = null;
	
	private function initService() {
		if ( is_null($this->galleryService) ) {
			//start the symfony kernel
			$kernel = static::createKernel();
			$kernel->boot();
			//get the DI container
			$container = $kernel->getContainer();
			$this->galleryService = $container->get('gallery_service');
		}
	}
	
}
?>