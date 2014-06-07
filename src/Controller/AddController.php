<?php

namespace Site\GalleryBundle\Controller;
use Site\GalleryBundle\Controller\DefaultController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\GalleryBundle\Entity\Image;
use Site\CoreBundle\Entity\UserConfigInfo as UserConfigInfo;

class AddController extends DefaultController {
	
	
	/**
	 * �������� ����� ���������
	 * @Template()
	 * @Secure(roles="ROLE_GAL_ADD_CAT")
	 * 
	 */
	public function addCategoryAction() {
		if ($user = $this->checkUserRole()) {
			// �������� ����� ���������
			$cat = new ImageCategory();
			$form = $this->createFormBuilder($cat)
			->add('name', 'text')
			->add('dirName', 'text')
			->getForm();
			if ($this->getRequest()->getMethod() === 'POST') {
				// ���� ������� ����� �� �����
				// ������������� ������� � �����
				$form->bind($this->getRequest());
				if ($form->isValid() && false) {
					// ������� ��������� � ���� ������
					$em = $this->getDoctrine()->getManager();
					$em->persist($cat);
					$em->flush();
					// �������� � ����� ���������
					return $this->redirect($this->generateUrl('site_gallery_category', array('id' => $cat->getId())));
				} else
					return array('form' => $form->createView());
			} else {
				// ���� �������������� �������� ��������
				return array('form' => $form->createView());
			}
		} else {
			// ������, ������������ ���� ��� ���������� ��������
			//$status = self::ST_ERR_NORULE;
			//throw $this->createNotFoundException('message');
			return $this->redirect($this->generateUrl('site_gallery_homepage'));
		}
	}
	
	/**
	 * Добавляет в категорию новый альбом
	 * @param string $cRefId
	 * @return multitype:\Symfony\Component\Form\FormView |\Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @Template()
	 * @Secure(roles="ROLE_GAL_ADD_ALB")
	 */
	public function addAlbumAction($cRefId) {
		/**
			Возможные ошибки:
			1. Недостаточно прав для выполнения операции (PermissionDeniedFault)
			2. Категория с таким именем не существует (DataNotFoundFault)
			3. Не задан идентификатор типа альбома (DataNotFoundFault)
			4. Тип альбома с таким имененм не существует (DataNotFoundFault)
			5. Ошибка при создании записи в БД (SystemInnerFault) PDOException
			6. Невозможно создать папку на сервере (SystemInnerFault) IOException
		 */
		try {
			$this->action = __FUNCTION__;
			$this->initVars();
			$logger = $this->get('gallery_add_logger');
			$this->getCategoryList(false);
			$this->body['categoryList'] = $this->categoryList;
			$this->getCategory($cRefId);
			$this->body['category'] = $this->category;
			$em = $this->getDoctrine()->getManager();
			// Если POST, то создаём альбом
			// Если GET, то выводим диалог выбор альбома
			if ($this->getRequest()->getMethod() === 'POST') {
				// Получение идентификатора типа альбома
				$dicItemRefId = $this->getRequest()->get('albums');
				$logger->warn(sprintf('%s (%d) пытается создать альбом "%s" в категории "%s"', $this->getUser()->getUsername(), $this->getUser()->getId(), $dicItemRefId, $this->category->getRefId()) );
				if (!$dicItemRefId)
					throw $this->createNotFoundException(sprintf('Не задан идентификатор типа альбома'));
				try {
					// Если такой альбом уже существует, то ошибка
					$album = $this->getAlbum($cRefId, $dicItemRefId);
					throw new NonUniqueResultException(sprintf('Альбом %s уже существует', $dicItemRefId));
				} catch (NonUniqueResultException $e) {
					$this->error[] = $e->getMessage() != '' ? $e->getMessage() : 'Дублирующиеся записи в БД! Ахтунг!';
					return new JsonResponse( $this->createResponse() );
				} catch (NoResultException $e) {}
				// Получение выбранной записи в choiceList
				$dicItem = $em->getRepository('SiteCoreBundle:DictionaryItem')->getItem(null, $dicItemRefId);
				// Создаём альбом
				$album = new ImageAlbum();
				// Установка категории
				$album->setCategory( $this->category )
				->setDictionary($dicItem)
				->setName($dicItem->getTitle())
				->setDirName($dicItem->getRefId())
				->setAllowAdd(false);
				// Добавление в очередь на загрузку в БД
				$em->persist( $album );
				$em->flush();
				$logger->info(sprintf('Альбом "%s" в категории "%s" успешно создан пользователем %s (%d)', $album->getDictionary()->getRefId(), $this->category->getRefId(), $this->getUser()->getUsername(), $this->getUser()->getId()) );
				$this->body['album'] = array(
						'id' => $album->getId(),
						'name' => $album->getName(),
						'category_name' => $album->getCategory()->getName(),
						'url' => $this->generateUrl('site_gallery_album', array('cRefId' => $album->getCategory()->getRefId(), 'aRefId' => $album->getDictionary()->getrefId()))
				);
			} else {				
				// Получение choice-list альбомов, исключая те, что уже созданы
				$choiceList = $em->getRepository('SiteGalleryBundle:ImageAlbum')->getAlbumsList( $this->category->getId() );
				$this->body['choices'] = $choiceList;				
			}
		} catch (\Exception $e) {
			$logger->error( sprintf('Ошибка при создании альбома пользователем %s (%d) - %s', $this->getUser()->getUsername(), $this->getUser()->getId(), $e->getMessage()) );
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		if ($this->getRequest()->getMethod() === 'POST')
			return new JsonResponse( $this->createResponse() );
		else return $this->createResponse();
	}
	
	/**
	 * Добавляет в альбом новые изображения
	 * @param string $cRefId
	 * @param string $aRefId
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|multitype:\Symfony\Component\Form\FormView |\Symfony\Component\HttpFoundation\Response
	 *
	 * @Template()
	 */
	public function addImagesAction($cRefId, $aRefId) {
		//if ( !$this->getUser()->isMod() )
		//	throw new AccessDeniedException();			
		try {
			$this->action = __FUNCTION__;
			$this->initVars();
			$logger = $this->get('gallery_add_logger');			
			$gallery = $this->get('gallery_service');
			$this->body['categoryList'] = $gallery->getCategoryList( false );
			$this->body['album'] = $gallery->getAlbum($cRefId, $aRefId);
			$logger->warn(sprintf('%s (%d) пытается добавить изображение в альбом "%s" в категории "%s"', $this->getUser()->getUsername(), $this->getUser()->getId(), $gallery->album->getDictionary()->getRefId(), $gallery->album->getCategory()->getRefId()) );
			if ( ( false === $this->get('security.context')->isGranted('ROLE_GAL_ADD_IMG') ) && ( false == $gallery->album->getAllowAdd() || 0 == $this->getUser()->getId() ) ) {
				throw new AccessDeniedException();
			}
 			$this->getUserSpace();
			$this->body['space'] = array(
				'total' => $this->totalSpace,
				'free' => $this->freeSpace,
				'occup' => $this->occupSpace
			);	
			$em = $this->getDoctrine()->getManager();	
			// Если POST, то загружаем изображение
			// Если GET, то выводим диалог загрузки
			if ($this->getRequest()->getMethod() === 'POST') {
				$user = $this->getUser();
				// Обработка файлов по-одному
				$count = 0;
				$image = new Image($count++);
				$image->setMemberId( $user->getId() )
					->setMemberName( $user->getUsername() )
					->setAlbum( $this->album )
					->setVisibility('hide')
 					->setFile( $this->getRequest()->files->get('image') );
				$errors = $this->get('validator')->validate( $image );				
				if ( count($errors) > 0 )
					throw new \Exception( (string) $errors );			
				if ($this->freeSpace < $image->getFile()->getClientSize() )
					throw new \Exception('Невозможно загрузить изображение. Превышена дисковая квота.');
				// Добавление в очередь на загрузку в БД
				$em->persist($image);
				$em->flush();
				$logger->info(sprintf('Новое изображение успешно добавлено в альбом "%s" в категории "%s" пользователем %s (%d)', $gallery->album->getDictionary()->getRefId(), $gallery->album->getCategory()->getRefId(), $this->getUser()->getUsername(), $this->getUser()->getId()) );
				$this->body['image'] = array(
					'id' => $image->getId(),
					'url' => $image->getWebPath(),
					'status' => $image->getVisibility(),
					'html' => $this->container->get('twig')->loadTemplate('SiteGalleryBundle:Show:showAlbum.html.twig')
						->renderBlock('image_block', array('image' => $image, 'imageHostName' => $this->imageHostName))
				);
			} else {
				//$this->body['image_template'] = $this->container->get('twig')->loadTemplate('SiteGalleryBundle:Show:showAlbum.html.twig')
				//		->renderBlock('image_block', array('image' => new Image(0), 'imgHostName' => $this->imageHostName));
			}
		} catch (\Exception $e) {
			$logger->error( sprintf('Ошибка при добавлении изображения пользователем %s (%d) - %s', $this->getUser()->getUsername(), $this->getUser()->getId(), $e->getMessage()) );
			$this->error[] = $e->getMessage();
			if ( $this->getUser()->isMod() )
				$this->error['trace'] = $e->getTraceAsString();
		}
		if ($this->getRequest()->getMethod() === 'POST')
			return new JsonResponse( $this->createResponse() );
		else return $this->createResponse();
	}
}
?>