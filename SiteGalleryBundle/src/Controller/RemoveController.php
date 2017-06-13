<?php

namespace Site\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class RemoveController extends Controller {
	/**
	 * Удаляет изображение
	 * 
	 * @param integer $imageId
	 * 
	 * @Template()
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
		$form = $this->createFormBuilder( $image )
			// TODO Задатки для symfony 2.6
// 			->add('save', 'submit', array('label' => 'Delete Image'))
			->getForm();
		if ( $this->getRequest()->getMethod() === 'POST') {
			$form->bind( $this->getRequest() );
			// Валидация данных с формы
			if ( $form->isValid() ) {
// 				if ( $form->get('remove')->isClicked() ) {
					try {
						$this->gallery->deleteImage( $image );
					} catch (\Exception $e) {
						throw new HttpException(200, sprintf('Невозможно выполнить операцию %s - ошибка выполнения', __METHOD__), $e);
					}
					return $this->redirect( $this->generateUrl('site_gallery_album', array('cRefId' => $this->gallery->category->getRefId(), 'aRefId' => $this->gallery->album->getDictionary()->getRefId())) );
// 				}
			}
		}
// 		return $this->gallery->getOutput();
		return array(
			'form' => $form->createView(),
			'imgHostName' => $this->gallery->imgHostName,
			'category' => $this->gallery->category,
			'album' => $this->gallery->album,
			'image' => $this->gallery->image );
	}
}
?>