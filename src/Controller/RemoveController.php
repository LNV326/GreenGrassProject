<?php

namespace Site\GalleryBundle\Controller;
use Monolog\Logger;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Site\GalleryBundle\Controller\DefaultController;
use Symfony\Component\HttpFoundation\JsonResponse;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Site\CoreBundle\Entity\UserConfigInfo as UserConfigInfo;

class RemoveController extends DefaultController {
	/**
	 * Удаляет изображение
	 * 
	 * @param integer $imageId
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function removeImageAction($imageId) {
		try {
			$this->action = __FUNCTION__;			
			$logger = $this->get('gallery_remove_logger');
			$em = $this->getDoctrine()->getManager();
			$image = $this->getImage($imageId, false, false);
			$logger->warn(sprintf('%s (%d) пытается удалить изображение id="%d" - владелец %s (%d)', $this->getUser()->getUsername(), $this->getUser()->getId(), $image->getId(), $image->getMemberName(), $image->getMemberId()) );
			if ( ( false === $this->get('security.context')->isGranted('ROLE_GAL_DEL_IMG') ) && ( $image->getMemberId() !== $this->getUser()->getId() ) ) {
				throw new AccessDeniedException();
			}			
			$em->remove( $image );
			$em->flush();
			$logger->info(sprintf('Изображение успешно удалено пользователем %s (%d)', $image->getId(), $this->getUser()->getUsername(), $this->getUser()->getId()) );
			$this->body['image'] = array(
				'id' => $image->getId(),
				'status' => 'Deleted'
			);
		} catch (\Exception $e) {
			$logger->error( sprintf('Ошибка при удалении изображения id="%d" пользователем %s (%d) - %s', $image->getId(), $this->getUser()->getUsername(), $this->getUser()->getId(), $e->getMessage()) );				
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		return new JsonResponse( $this->createResponse() );
	}
}
?>