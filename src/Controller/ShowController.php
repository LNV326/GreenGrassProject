<?php

namespace Site\GalleryBundle\Controller;
use Site\GalleryBundle\Controller\DefaultController;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Site\GalleryBundle\Entity\ImageCategory;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Doctrine\Common\Collections\ArrayCollection;

class ShowController extends DefaultController {
	
	protected $action;
	protected $imgHost;
	protected $categoryList;
	protected $body = array();
	protected $error = array();
	
	protected function onLoad() {
		$this->body['action'] = __METHOD__;
		$this->body['imageHostName'] = $this->container->getParameter('img_host');
		$this->gallery = $this->get('gallery_service');
	}
	
	/**
	 * Главная страница галереи
	 * Возвращает список категорий галереи
	 *
	 * @Template()
	 */
	public function showCatalogAction() {
		// === Редирект ===
		/*
		 * Список примеров URL   страниц старой галереи
		 * http://images.nfsko.ru/index.php?page=gallery&cat=17
		 * http://images.nfsko.ru/index.php?page=gallery&view=160
		 * http://images.nfsko.ru/index.php?page=gallery&user=22753
		 * http://images.nfsko.ru/index.php?page=gallery&view=my_files
		 * http://images.nfsko.ru/image.php?gallery=view&image=1542
		 */
		$redirect = $this->get('redirect_service');
		if ( !is_null( $r = $redirect->getRedirect() ) )
			// Can throw NotFoundHttpException
			return $this->redirect( $this->generateUrl( $r['route'], $r['arg'] ), $r['redirect_code'] );
		// === Редирект (конец) ===
		$this->onLoad();						
		$this->body['categoryList'] = $this->gallery->getCategoryList( array('withCovers' => true) );				
		return $this->createResponse();
	}

	/**
	 * Возвращает категорию и список альбомов в ней
	 * @param string $cRefId
	 * @return multitype:unknown \Symfony\Component\DependencyInjection\mixed
	 * 
	 * @Template()
	 */
	public function showCategoryAction($cRefId) {
		$this->onLoad();
		$this->body['categoryList'] = $this->gallery->getCategoryList( array('withCovers' => false) );
		try {
			$this->body['category'] = $this->gallery->getCategory( $cRefId, array('withAlbums' => true, 'withCovers' => true) );
			$this->body['albumList'] = $this->gallery->albumList;
		} catch (NoResultException $e) {
			throw $this->createNotFoundException( sprintf('Категория %s не существует',$cRefId), $e );
		}
		return $this->createResponse();
	}

	/**
	 * Возвращает альбом и изображения в нём
	 * @param string $cRefId
	 * @param string $aRefId
	 * @return multitype:NULL unknown \Symfony\Component\DependencyInjection\mixed
	 * 
	 * @Template()
	 */
	public function showAlbumAction($cRefId, $aRefId) {
		$this->onLoad();
		$this->body['categoryList'] = $this->gallery->getCategoryList( array('withCovers' => false) );
		
		$query = $this->getRequest()->query;
		If ( $query->has('page') ) {
			$page = (int)$query->get('page');
			if ( $page <= 0 ) $page = 1;
		} else $page = 1;
		$this->body['countOnPage'] = $this->gallery->IMGS_ON_PAGE;
		$this->body['firstItem'] = ($page-1)*$this->body['countOnPage'];
		$this->body['lastItem'] = ($page)*$this->body['countOnPage']-1;
		
 		try {			
			$this->body['album'] = $this->gallery->getAlbum($cRefId, $aRefId, array('withImages' => true, 'withCovers' => false) );
			$this->body['category'] = $this->gallery->category;
			$this->body['imageList'] = $this->gallery->imageList;
		} catch (NoResultException $e) {
			throw $this->createNotFoundException( sprintf('Альбома %s в категории %s не существует', $aRefId, $cRefId), $e );
		}
		if ( is_array($this->body['imageList']))
			$this->body['pages'] = ceil( count($this->body['imageList'])/$this->body['countOnPage'] );
		else $this->body['pages'] = 1;
		$this->body['page'] = $page;
		
		return $this->createResponse();
	}
	
	/**
	 * Возвращает изображение
	 * @param unknown $iId
	 * @throws AccessDeniedHttpException
	 * @return \Site\GalleryBundle\Controller\Response
	 * 
	 * @Template()
	 */
	public function showImageAction($iId) {
		$this->onLoad();
		$this->body['categoryList'] = $this->gallery->getCategoryList( array('withCovers' => false) );
		try {
			$this->body['image'] = $this->gallery->getImage( $iId );
			$this->body['category'] = $this->gallery->category;
			$this->body['album'] = $this->gallery->album;
		} catch (NoResultException $e) {
			throw $this->createNotFoundException( sprintf('Изображение %s не существует', $iId), $e );
		}
		return $this->createResponse();
	}
	
	/**
	 * Возвращает загруженные пользователем изображения
	 * @param unknown $uId
	 * @return multitype:string multitype:
	 * 
	 * @Template()
	 * @Secure(roles="ROLE_GAL_USER_SHOW")
	 */
	public function showUserImagesAction($uId) {
		$this->onLoad();
		$this->body['categoryList'] = $this->gallery->getCategoryList( array('withCovers' => false) );
		// Необходимо получить пользователя
		// TODO Необходимо вынести получение информации о пользователе в родительский класс
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteCoreBundle:UserConfigInfo');
		if ( is_null( $user = $repo->find( $uId ) ) )
			throw $this->createNotFoundException( sprintf('Пользователь %s не существует', $uId) );		
		$this->body['user'] = $user;
		$this->body['imageList'] = $this->gallery->getUserImageList( $user );			
		return $this->createResponse();
	}

// 	/**
// 	 * пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅ пїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅ пїЅпїЅпїЅ
// 	 * @param integer $id пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ
// 	 *
// 	 *	@Template()
// 	 */
// 	public function showHistoryAction() {
// 		$this->imageHostName = $this->container->getParameter('img_host');
// 		$em = $this->getDoctrine()->getManager();
// 		$images = $em->getRepository('SiteGalleryBundle:Image')
// 				->getMonthHistory();
// 		return array('images' => $images, 'imgHostName' => $this->imageHostName);

// 	}
}
