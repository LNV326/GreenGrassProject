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
	
	/**
	 * Главная страница галереи
	 * Возвращает список категорий галереи
	 *
	 * @Template()
	 * @Secure(roles="ROLE_GAL_CATS_SHOW")
	 */
	public function showCatalogAction() {
		// === Редирект ===
		/*
		 * Список примеров URL страниц старой галереи
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
					
		try {
			$this->action = __FUNCTION__;
			$this->initVars();		
			$gallery = $this->get('gallery_service');					
			$this->body['categoryList'] = $gallery->getCategoryList( true );			
			$this->body['imageHostName'] = $this->imageHostName;
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}	
		return $this->createResponse();
	}

	/**
	 * Возвращает категорию и список альбомов в ней
	 * @param string $cRefId
	 * @return multitype:unknown \Symfony\Component\DependencyInjection\mixed
	 * 
	 * @Template()
	 * @Secure(roles="ROLE_GAL_ALBS_SHOW")
	 */
	public function showCategoryAction($cRefId) {
		try {
			$this->action = __FUNCTION__;
			$this->initVars();
			$gallery = $this->get('gallery_service');
			$this->body['categoryList'] = $gallery->getCategoryList( false );
			$this->body['category'] = $gallery->getCategory($cRefId, true, true);			
			$this->body['imageHostName'] = $this->imageHostName;
		//} catch (\NoResultException $e) {
		//	throw $this->createNotFoundException(sprintf('Категория %s не существует',$cRefId));
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
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
	 * @Secure(roles="ROLE_GAL_IMGS_SHOW")
	 */
	public function showAlbumAction($cRefId, $aRefId) {
		try {
			$this->action = __FUNCTION__;
			$this->initVars();
			$gallery = $this->get('gallery_service');
			$this->body['categoryList'] = $gallery->getCategoryList( false );
			$this->body['album'] = $gallery->getAlbum($cRefId, $aRefId, true);			
			$this->body['imageHostName'] = $this->imageHostName;
		//} catch (\NoResultException $e) {
		//	throw $this->createNotFoundException(sprintf('Категория %s не существует',$cRefId));
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		return $this->createResponse();
	}
	
	/**
	 * Возвращает изображение
	 * @param unknown $iId
	 * @throws AccessDeniedHttpException
	 * @return \Site\GalleryBundle\Controller\Response
	 * 
	 * @Template()
	 * @Secure(roles="ROLE_GAL_IMGS_SHOW")
	 */
	public function showImageAction($iId) {
		//if ( !$this->getRequest()->isXmlHttpRequest() )
		//	throw new AccessDeniedHttpException();
		try {
			$this->action = __FUNCTION__;
			$this->initVars();
			$gallery = $this->get('gallery_service');
			$this->body['categoryList'] = $gallery->getCategoryList( false );
			$this->body['image'] = $gallery->getImage($iId, false, false);		
			$this->body['imageHostName'] = $this->imageHostName;
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
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
		try {
			$this->action = __FUNCTION__;
			$this->initVars();
			$gallery = $this->get('gallery_service');
			$this->body['categoryList'] = $gallery->getCategoryList( false );
			
			// Необходимо получить пользователя
			// TODO Необходимо вынести получение информации о пользователе в родительский класс
			$repo = $this->getDoctrine()->getManager()->getRepository('SiteCoreBundle:UserConfigInfo');
			if ( is_null( $user = $repo->find( $uId ) ) )
				throw new NoResultException();		
			$this->body['user'] = $user;
			$this->body['images'] = $gallery->getUserImages( $uId, false, false );			
			$this->body['imageHostName'] = $this->imageHostName;
		} catch (\Exception $e) {
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
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
