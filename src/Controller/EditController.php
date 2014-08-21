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
	 * @Secure(roles="ROLE_MODERATOR")
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
	 * @Secure(roles="ROLE_MODERATOR")
	 */
	public function setAlbumCoverAction($cRefId, $aRefId, $iId) {
		$this->gallery = $this->get('gallery_service');
		try {
			$album = $this->gallery->getAlbum( $cRefId, $aRefId );
			$image = $this->gallery->getImage( $iId );
		} catch (NoResultException $e) {
			throw $this->createNotFoundException( sprintf('Невозможно выполнить операцию %s - не найдены объекты в системе', __METHOD__), $e );
		}
		try {
			$this->gallery->setAlbumCover( $album, $image );
		} catch (\Exception $e) {
			throw new \Exception( sprintf('Невозможно выполнить операцию %s - ошибка выполнения', __METHOD__), 0, $e);
		}
		return $this->gallery->getOutput();
	}
	
	/**
	 * Устанавливает обложку для категории
	 * @param unknown $cRefId
	 * 
	 * @Secure(roles="ROLE_MODERATOR")
	 */
	public function setCategoryCoverAction($cRefId, $iId) {
		$this->gallery = $this->get('gallery_service');
		try {
			$category = $this->gallery->getCategory( $cRefId );
			$image = $this->gallery->getImage( $iId );
		} catch (NoResultException $e) {
			throw $this->createNotFoundException( sprintf('Невозможно выполнить операцию %s - не найдены объекты в системе', __METHOD__), $e );
		}
		try {
			$this->gallery->setCategoryCover( $category, $image );
		} catch (\Exception $e) {
			throw new \Exception( sprintf('Невозможно выполнить операцию %s - ошибка выполнения', __METHOD__), 0, $e);
		}
		return $this->gallery->getOutput();
	}
}
?>