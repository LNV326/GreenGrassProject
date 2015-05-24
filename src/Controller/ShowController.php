<?php

namespace Site\GalleryBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Doctrine\Common\Collections\ArrayCollection;

class ShowController extends Controller {
	
	const ERR_CATEGORY = 'Категория "%s не найдена';
	const ERR_ALBUM = 'Альбом "%s" в категории "%s" не найден';
	const ERR_IMAGE = 'Изображение "%s" не найдено';

	protected $gallery = null;
	
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
		$this->gallery = $this->get('gallery_service');						
		$this->body['categoryList'] = $this->gallery->getCategoryList( array('withCovers' => true) );	
		$this->get('session')->getFlashBag()->add(
				'info',
				'Здравствуйте! Галерея изображений NFSKO находится на этапе разработки. Если вы нашли ошибку, у вас замечания или предложения, просьба писать их в <a href="http://www.nfsko.ru/forum/index.php?showtopic=8246">этой теме</a> на нашем форуме.'
		);
		return $this->gallery->getOutput();
	}

	/**
	 * Возвращает категорию и список альбомов в ней
	 * @param string $cRefId
	 * @return multitype:unknown \Symfony\Component\DependencyInjection\mixed
	 * 
	 * @Template()
	 */
	public function showCategoryAction($cRefId) {
		$this->gallery = $this->get('gallery_service');
		$this->gallery->getCategoryList( array('withCovers' => false) );
		$category = $this->gallery->getCategory( $cRefId, array('withAlbums' => true, 'withCovers' => true) );
		if ( is_null($category) )
			throw $this->createNotFoundException( sprintf(self::ERR_CATEGORY, $cRefId) );
		return $this->gallery->getOutput();
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
		$query = $this->getRequest()->query;
		If ( $query->has('page') ) {
			$page = (int)$query->get('page');
			if ( $page <= 0 ) $page = 1;
		} else $page = 1;			
		$this->gallery = $this->get('gallery_service');
		$this->gallery->getCategoryList( array('withCovers' => false) );
		$pageInfo['countOnPage'] = $this->gallery->IMGS_ON_PAGE;
		$pageInfo['firstItem'] = ($page-1)*$pageInfo['countOnPage'];
		$pageInfo['lastItem'] = ($page)*$pageInfo['countOnPage']-1;
		$album = $this->gallery->getAlbum($cRefId, $aRefId, array('withImages' => true, 'withCovers' => false) );
		if ( is_null($album) )
			throw $this->createNotFoundException( sprintf(self::ERR_ALBUM, $aRefId, $cRefId) );	
		if ( is_array($this->gallery->imageList) && count($this->gallery->imageList) > 0 )
			$pageInfo['pages'] = ceil( count($this->gallery->imageList)/$pageInfo['countOnPage'] );
		else $pageInfo['pages'] = 1;
		$pageInfo['page'] = $page;	
		return array_merge($this->gallery->getOutput(), array('pageInfo' => $pageInfo));
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
		$this->gallery = $this->get('gallery_service');
		$this->gallery->getCategoryList( array('withCovers' => false) );
		$image = $this->gallery->getImage( $iId );
		if ( is_null($image) )
			throw $this->createNotFoundException( sprintf(self::ERR_IMAGE, $iId), $e );
		return $this->gallery->getOutput();
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
		$this->gallery = $this->get('gallery_service');
		$this->gallery->getCategoryList( array('withCovers' => false) );
		// Необходимо получить пользователя
		// TODO Необходимо вынести получение информации о пользователе в родительский класс
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteCoreBundle:UserConfigInfo');
		if ( is_null( $user = $repo->find( $uId ) ) )
			throw $this->createNotFoundException( sprintf('Пользователь %s не существует', $uId) );		
		$this->body['imageList'] = $this->gallery->getUserImageList( $user );			
		return array_merge($this->gallery->getOutput(), array('userInfo' => $user));
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
