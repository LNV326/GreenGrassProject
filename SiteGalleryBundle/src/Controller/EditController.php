<?php
namespace Site\GalleryBundle\Controller;

use Symfony\Component\BrowserKit\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use JMS\SecurityExtraBundle\Annotation\Secure;
//use Doctrine\Common\Collections\ArrayCollection;

class EditController extends Controller {
			
	/**
	 * Скрывает изображение
	 * @Secure(roles="ROLE_MODERATOR")
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
	private function toggleVisibilityImage($imageId, $visible) {
		$this->gallery = $this->get('gallery_service');
		$image = $this->gallery->getImage( $imageId );
		if ( is_null($image) )
			throw $this->createNotFoundException( sprintf('Невозможно выполнить операцию %s - не найдены объекты в системе', __METHOD__) );
		try {
			$this->gallery->setImageVisibility( $image, $visible );
		} catch (\Exception $e) {
			throw new \Exception( sprintf('Невозможно выполнить операцию %s - ошибка выполнения', __METHOD__), 0, $e);
		}
		return $this->gallery->getOutput();
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
		$album = $this->gallery->getAlbum( $cRefId, $aRefId );
		$image = $this->gallery->getImage( $iId );
		if ( is_null($album) || is_null($image) )
			throw $this->createNotFoundException( sprintf('Невозможно выполнить операцию %s - не найдены объекты в системе', __METHOD__) );
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
		$category = $this->gallery->getCategory( $cRefId );
		$image = $this->gallery->getImage( $iId );
		if ( is_null($category) || is_null($image) )
			throw $this->createNotFoundException( sprintf('Невозможно выполнить операцию %s - не найдены объекты в системе', __METHOD__) );
		try {
			$this->gallery->setCategoryCover( $category, $image );
		} catch (\Exception $e) {
			throw new \Exception( sprintf('Невозможно выполнить операцию %s - ошибка выполнения', __METHOD__), 0, $e);
		}
		return $this->gallery->getOutput();
	}
}
?>