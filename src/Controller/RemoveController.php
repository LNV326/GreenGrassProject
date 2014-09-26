<?php

namespace Site\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\SecurityExtraBundle\Annotation\Secure;

class RemoveController extends Controller {
	/**
	 * Удаляет изображение
	 * 
	 * @param integer $imageId
	 * @return \Symfony\Component\HttpFoundation\Response
	 * 
	 * @Secure(roles="ROLE_MEMBER")
	 */
	public function removeImageAction($imageId) {
		$this->gallery = $this->get('gallery_service');
		$image = $this->gallery->getImage( $imageId );
		if ( is_null($image) )
			throw $this->createNotFoundException( sprintf('Невозможно выполнить операцию %s - не найдены объекты в системе', __METHOD__) );
		// Проверка прав доступа
		if ( ( false === $this->get('security.context')->isGranted('ROLE_GAL_DEL_IMG') ) && ( $image->getMemberId() !== $this->getUser()->getId() ) ) {
			throw new AccessDeniedException();
		}
		try {
			$this->gallery->deleteImage( $image );
		} catch (\Exception $e) {
			throw new \Exception( sprintf('Невозможно выполнить операцию %s - ошибка выполнения', __METHOD__), 0, $e);
		}
		return $this->gallery->getOutput();
	}
}
?>