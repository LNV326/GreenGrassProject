<?php

namespace Site\GalleryBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Response;

use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\GalleryBundle\Entity\Image;
use Site\CoreBundle\Entity\UserConfigInfo as UserConfigInfo;

class ManageController extends Controller {
	
	const ST_OK = 0;
	const ST_ERR_NORULE = 1;

	// ============ ��������� ============

	/**
	 * ����������� ���������
	 * @param $id_cat ������������� ���������
	 * @Template()
	 */
	public function removeCategoryAction($catId) {
		if ($user = $this->checkUserRole() && false) {
			// ��������� ���������
			$em = $this->getDoctrine()->getManager();
			$cat = $em->getRepository('SiteGalleryBundle:ImageCategory')
			->find($catId);
			if ($cat) {
				// �������� ������� � ��
				// �����! �������� ������� ����� �������, ��� ��� ��������
				// ��������� ��� ��� ������� ��������� �������������
				$em->remove($cat);
				$em->flush();
				// TODO ���������� �����
			} else {
				// TODO ������, ����� ��������� �� ����������
			}
		} else {
			// ������, ������������ ���� ��� ���������� ��������
			//$status = self::ST_ERR_NORULE;
			return $this->redirect($this->generateUrl('site_gallery_homepage'));
		}
	}

	// ============ ������ ============

	/**
	 * @Template()
	 */
	public function removeAlbumAction($albumId) {
		if ($user = $this->checkUserRole()) {
			// ��������� �������
			$em = $this->getDoctrine()->getManager();
			$album = $em->getRepository('SiteGalleryBundle:ImageAlbum')
			->find($albumId);
			if ($album) {
				// �������� ������� � ��
				// �����! �������� ������� ����� �������, ��� ��� ��������
				// ������� ��� ��� ����������� ��������� �������������
				$em->remove($album);
				$em->flush();
				// TODO ���������� �����
			} else {
				// TODO ������, ������ ������� �� ����������
			}
		} else {
			// ������, ������������ ���� ��� ���������� ��������
			//$status = self::ST_ERR_NORULE;
			return $this->redirect($this->generateUrl('site_gallery_category', array('id' => $catId)));
		}
	}

	// ============ ����������� ============


	/**
	 * ����������� ���� ��������� ����������� � ��������� ������ 
	 */
	public function moveImagesAction($albumId) {
		if ($user = $this->checkUserRole()) {
			$ids = $this->getImagesIDs();
			$request = $this->getRequest();
			$template = $this->get('twig')->loadTemplate('SiteGalleryBundle:Manage:moveImages.html.twig');
			// ���� ����� POST - ���������� ���������
			if ($this->getRequest()->getMethod() === 'POST') {
				
			} else {
				// ...e��� ����� GET - ������ �������������			
				if (count($ids['move']) > 0) {
					// ��������� ������ � ���� ������������
					$images = $em->getRepository('SiteGalleryBundle:Image')->findImagesById($ids['move']);
					// ��������� ������ � ���������� � �������������
					$cats = $em->getRepository('SiteGalleryBundle:ImageCategory')
					->findCatsWithSubcats();					
				} else {
					$images = array();
					$cats = array();
				}
				// ��������� ���� ��������� ����������� � ������ ���������/������������
				$result = $template
				->renderBlock('confirm', array(
						'images' => $images,
						'imgHostName' => Image::HOST_ADDRESS,
						'cats' => $cats
				));
				// ����������� ������� JSON
				return new Response(json_encode(array(
						'result' => $result,
						'errors' => null
				)));
			}
			
			
// 			// ���� ����������� ��� ����������� �������
// 			if ($ids != null && $request->has('to_album_id')) {
// 				$toAlbumId = get('to_album_id');
// 				// ��������� ������� � ��� ����������
// 				$em = $this->getDoctrine()->getManager();
// 				$album = $em->getRepository('SiteGalleryBundle:ImageAlbum')
// 						->find($toAlbumId);
// 				// ���� ��������� ������ ����������
// 				if ($album) {
// 					// ��������� ���� ������������ ������ ID �������
// 					$em->getRepository('SiteGalleryBundle:Image')
// 							->moveImages($ids, $toAlbumId);
// 				} else {
// 					// TODO ������, ������� ������� �� ����������
// 				}
// 			} else {
// 				// ������, �� ������� �� ������ ��������
// 				$status = self::ST_ERR_EMPTY;
// 			}
		} else {
			// ������, ������������ ���� ��� ���������� ��������
			$status = self::ST_ERR_NORULE;
		}
		return $this->checkStatus($status);
	}

	/**
	 * ��������� ������� �����������
	 */	
	public function setStatusImagesAction($albumId) {
		if ($user = $this->checkUserRole()) {
			// ��������� ������� � id �����������
			$ids = $this->getImagesIDs();
			$em = $this->getDoctrine()->getManager();
			$template = $this->get('twig')->loadTemplate('SiteGalleryBundle:Manage:setStatusImages.html.twig');
			// ���� ����� POST - ���������� ���������
			if ($this->getRequest()->getMethod() === 'POST') {
				// ��������� ���� ������������ ������ �������
				$em = $this->getDoctrine()->getManager();	
				$result = array();					
				if (count($ids['show']) > 0) {
					$result['show'] = $em->getRepository('SiteGalleryBundle:Image')
						->setStatusImages($ids['show'], 'show');
				}
				if (count($ids['hide']) > 0) {
					$result['hide'] = $em->getRepository('SiteGalleryBundle:Image')
						->setStatusImages($ids['hide'], 'hide');
				}
				if (count($ids['trash']) > 0) {
					$result['trash'] = $em->getRepository('SiteGalleryBundle:Image')
						->setStatusImages($ids['trash'], 'trash');
				}
				// ������������ ������ � �����������
				$result = $template
				->renderBlock('result', array(
						'statuses' => $ids,
						'result' => $result,
				));
				
				// ����������� ������� JSON
				return new Response(json_encode(array(
						'result' => $result,
						'errors' => null
				)));
				$status = self::ST_OK;
			} else {
				// ...e��� ����� GET - ������ �������������
				// ��������� ������ � ���� ������������
				$allIds = array_merge($ids['show'], $ids['hide'], $ids['trash']);
				if (count($allIds) > 0)
					$images = $em->getRepository('SiteGalleryBundle:Image')->findImagesById($allIds);
				else $images = array();
				// ��������� ���� ����������� � ������ ��������� ��� �������������
				$result = $template
				->renderBlock('confirm', array(
						'images' => $images,
						'statuses' => $ids,
						'imgHostName' => Image::HOST_ADDRESS
				));
				// ����������� ������� JSON
				return new Response(json_encode(array(
						'result' => $result,
						'errors' => null
				)));
			}
		} else {
			// ������, ������������ ���� ��� ���������� ��������
			$status = self::ST_ERR_NORULE;
		}
		return $this->checkStatus($status);
	}

	/**
	 * @Template()
	 * �������� ���� �����������, ����������� � ����������� ������� (������������ � ��������)
	 */
	public function removeImagesAction($albumId) {
		if ($user = $this->checkUserRole()) {
			$ids = $this->getImagesIDs();
			// ���� ����������� ��� �������� �������
			if ($ids != null) {
				// ��������� ��������� �����������	
				$em = $this->getDoctrine()->getManager();
				$images = $em->getRepository('SiteGalleryBundle:Image')
						->getTrashImagesByIDs($ids);
				// �������� ��������� �����������
				foreach ($images as $image)
					$em->remove($image);
				$em->flush();
			} else {
				// ������, �� ������� �� ������ ��������
				$status = self::ST_ERR_EMPTY;
			}
			//return $this->redirect($this->generateUrl('site_gallery_album', array('id' => $albumId)));
		} else {
			// ������, ������������ ���� ��� ���������� ��������
			$status = self::ST_ERR_NORULE;
		}
		return $this->checkStatus($status);
	}

	/**
	 * ���������� ������ � ���������� ��������� ����������� � ID ����������� ��� ������� �������.
	 * @return array
	 */
	protected function getImagesIDs() {
		$request = $this->getRequest();
		$ids = array(
				'show' => $request->get('showIds'),
				'hide' => $request->get('hideIds'),
				'move' => $request->get('moveIds'),
				'trash' => $request->get('trashIds')
		);
		foreach($ids as $key => $array) {
			// ���� ����� �� ������, ������� ����
			if (!is_array($array))
				$ids[$key] = array();
		}
		return $ids;
	}

	/**
	 * ��������� ����� �������� ������������ �� ���������� ���������������� �������
	 * ���������� ���������� � ������� ������������
	 * @return Symfony\Component\Security\Core\User\UserInterface|NULL
	 */
	protected function checkUserRole() {
		// ��������� ���������� � ������������
		$user = $this->getUser();
		//TODO ������ true �������� ����� �� �������� ������
		if ($user instanceof UserConfigInfo) {
			return $user;
		} else
			return null;
	}

	/**
	 * �������� ID �����������, ������� ���������� �������� ������, � ��������� ���������
	 * ���������� ������ ����������
	 * @param string $status
	 * @return integer
	 */
	protected function setImageStatus($status) {
		$ids = $this->getImagesIDs();
		if ($ids != null) {
			// ��������� ���� ������������ ����� ����������� � �������
			$em = $this->getDoctrine()->getManager();
			$em->getRepository('SiteGalleryBundle:Image')
					->setStatusImages($ids, $status);
			return self::ST_OK;
		} else {
			// ������, �� ������� �� ������ ��������
			return self::ST_ERR_EMPTY;
		}
		

		if (length($ids['moveIds']) > 0) {
			$em->getRepository('SiteGalleryBundle:Image')
				->setStatusImages($ids['moveIds'], 'move');
		}
	}
	
	protected function checkStatus($status) {
		switch ($status) {
			case self::ST_ERR_NORULE : {
				return new Response(json_encode(array(
						'result' => 'No Rule to do this action'
				)));
			}
		}
	}
}
