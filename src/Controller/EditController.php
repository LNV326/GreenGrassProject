<?php
namespace Site\GalleryBundle\Controller;
use Symfony\Component\BrowserKit\Response;

use Site\GalleryBundle\Controller\DefaultController;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
//use Doctrine\Common\Collections\ArrayCollection;

use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\GalleryBundle\Entity\Image;
use Site\CoreBundle\Entity\UserConfigInfo as UserConfigInfo;

class EditController extends DefaultController {
		
	// Возможные статусы видимости
	const VSB_SHOW = 'show';
	const VSB_HIDE = 'hide';
	
	/**
	 * Скрывает изображение
	 * @Secure(roles="ROLE_GAL_EDIT_IMG")
	 */
	public function hideImageAction($imageId) {
		$this->action = __FUNCTION__;
		return $this->toggleVisibilityImage($imageId, false);
	}
	
	/**
	 * Отображает изображение
	 * @Secure(roles="ROLE_GAL_EDIT_IMG")
	 */
	public function showImageAction($imageId) {
		$this->action = __FUNCTION__;
		return $this->toggleVisibilityImage($imageId, true);
	}
	
	/**
	 * Скрывает/отображает изображение
	 */
	private function toggleVisibilityImage($imageId, $visibility) {		
		$em = $this->getDoctrine()->getEntityManager();
		$logger = $this->get('gallery_edit_logger');
		try {
			$logger->warn(sprintf('%s (%d) пытается изменить видимость изображения id="%d"', $this->getUser()->getUsername(), $this->getUser()->getId(), $imageId) );
			$image = $this->getImage($imageId);			
			$image->setVisibility($visibility); // Скрытие TODO Да, тут косяк с инверсией
			$em->persist($image);
			$em->flush();
			$logger->info(sprintf('У изображения id="%d" успешно изменена видимость пользователем %s (%d)', $image->getId(), $this->getUser()->getUsername(), $this->getUser()->getId()) );
			$this->body = array(
				'image_id' => $image->getId(),
				'image_visibility' => $image->getVisibility() ? self::VSB_SHOW : self::VSB_HIDE
			);
		} catch (\Exception $e) {
			$logger->error( sprintf('Ошибка при изменении видимости изображения id="%d" пользователем %s (%d) - %s', $imageId, $this->getUser()->getUsername(), $this->getUser()->getId(), $e->getMessage()) );
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		return new JsonResponse( $this->createResponse() );
	}
	
	/**
	 * Устанавливает обложку для альбома
	 * @param unknown $cRefId
	 * @param unknown $aRefId
	 * 
	 * @Secure(roles="ROLE_GAL_EDIT_ALB")
	 */
	public function setAlbumCoverAction($cRefId, $aRefId, $iId) {
		$this->action = __FUNCTION__;
		$this->initVars();
		$id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid
		$em = $this->getDoctrine()->getManager();
		$logger = $this->get('gallery_edit_logger');
		try {
			$logger->warn(sprintf('%s (%d) пытается установить изображение id="%d" как обложку альбома "%s" в категории "%s"', $this->getUser()->getUsername(), $this->getUser()->getId(), $iId, $aRefId, $cRefId) );
			$image = $this->getImage($iId);
			$album = $this->getAlbum($cRefId, $aRefId);
			$album->setCoverImage($image);
			$em->persist($album);
			$em->flush();
			$logger->info(sprintf('Изображение id="%d" успешно установлено обложкой альбома "%s" в категории "%s" пользователем %s (%d)', $image->getId(), $album->getDictionary()->getRefId(), $album->getCategory()->getRefId(), $this->getUser()->getUsername(), $this->getUser()->getId()) );
		} catch (\Exception $e) {
			$logger->error( sprintf('Ошибка при установке изображения id="%d" обложкой альбома "%s" в категории "%s" пользователем %s (%d) - %s', $iId, $aRefId, $cRefId, $this->getUser()->getUsername(), $this->getUser()->getId(), $e->getMessage()) );
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		return new JsonResponse( $this->createResponse() );
	}
	
	/**
	 * Устанавливает обложку для категории
	 * @param unknown $cRefId
	 * 
	 * @Secure(roles="ROLE_GAL_EDIT_CAT")
	 */
	public function setCategoryCoverAction($cRefId, $iId) {
		$this->action = __FUNCTION__;
		$this->initVars();
		$id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid
		$em = $this->getDoctrine()->getManager();
		$logger = $this->get('gallery_edit_logger');
		try {
			$logger->warn(sprintf('%s (%d) пытается установить изображение id="%d" как обложку категории "%s"', $this->getUser()->getUsername(), $this->getUser()->getId(), $iId, $cRefId) );
			$image = $this->getImage($iId);	
			$category = $this->getCategory($cRefId);
			$category->setCoverImage($image);
			$em->persist($category);
			$em->flush();
			$logger->info(sprintf('Изображение id="%d" успешно установлено обложкой категории "%s" пользователем %s (%d)', $image->getId(), $category->getRefId(), $this->getUser()->getUsername(), $this->getUser()->getId()) );
		} catch (\Exception $e) {
			$logger->error( sprintf('Ошибка при установке изображения id="%d" обложкой категории "%s" пользователем %s (%d) - %s', $iId, $cRefId, $this->getUser()->getUsername(), $this->getUser()->getId(), $e->getMessage()) );
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		return new JsonResponse( $this->createResponse() );
	}
}
?>