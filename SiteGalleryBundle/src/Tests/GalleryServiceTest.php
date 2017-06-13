<?php

namespace Site\GalleryBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;

class GalleryServiceTest extends WebTestCase {

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
	
	/* ===============
	 * Array Tests
	   =============== */
	
	public function testGetCategoryList() {
		$this->initService();
		$categoryList = $this->galleryService->getCategoryList();
		$this->assertNotNull( $categoryList );
		return $categoryList;
	}
	
	/**
	 * @depends testGetCategoryList
	 */
	public function testCategoryListProvider( $categoryList ) {
		$this->assertNotNull( $categoryList );
		$this->assertNotEmpty( $categoryList );
		$cl = array();
		foreach ($categoryList as $catalog)
			$cl[ $catalog->getName() ] = array( $catalog );
		return $cl;
	}
	
	/**
	 * @dataProvider testGetCategoryList2
	 */
	public function testCategoryItem( $category ) {
		$this->assertInstanceOf('Site\GalleryBundle\Entity\ImageCategory', $category);
		$category = $this->galleryService->getCategory( $category->getRefId() );
		return $category;
	}
	
	
	
	/* ===============
	 * Tests
	=============== */
	
// 	dataProvider getCatalogList

	/**
	 * @depends testGetCategoryList
	 */
	public function testGetCategoryList2( $categoryList ) {
// 		$this->assertInstanceOf('Site\GalleryBundle\Entity\ImageCategory', $item);
		$this->assertNotNull( $categoryList );
		$cl = array();
		foreach ($categoryList as $catalog)
			$cl[ $catalog->getName() ] = array( $catalog );
		return $cl;
	}
	
// 	/**
// 	 * @depends testT2
// 	 */
// 	public function testT3( $item ) {
// 		$this->assertNotNull( $item );
// 	}
}
?>