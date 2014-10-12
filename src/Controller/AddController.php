<?php

namespace Site\GalleryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\FlattenException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\GalleryBundle\Entity\Image;
use Site\GalleryBundle\GalleryService;

class AddController extends Controller {
	
	protected $gallery = null;
	protected $form = null;
	
	/* ================================
	 * Add Category
	================================ */
	
	/**
	 * �������� ����� ���������
	 * @Template()
	 * @Secure(roles="ROLE_GAL_CATS_ADD")
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
	
	/* ================================
	 * Add Album
	================================ */
	
	/**
	 * Добавляет в категорию новый альбом
	 * @param string $cRefId Идентификатор категории
	 * @return multitype:\Symfony\Component\Form\FormView |\Symfony\Component\HttpFoundation\RedirectResponse
	 *
	 * @Template()
	 * @Secure(roles="ROLE_GAL_ALBS_ADD")
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
			ErrorException
			InvalidArgumentException
		 */
		$this->addAlbum_PreAction($cRefId);
		$this->gallery->debugMode = true;
		if ($this->getRequest()->getMethod() === 'POST') {
			// Получение идентификатора типа альбома и проверка существования его в справочнике
			$dicItemRefId = $this->getRequest()->get('albums');
			// Добавление альбома в категорию
			try {
				$this->addAlbum_DoAction($cRefId, $dicItemRefId);
				$this->get('session')->getFlashBag()->add(
					'success',
					$this->gallery->album
				);
			} catch (\Exception $e) {
				$this->get('session')->getFlashBag()->add(
					'failure',
					FlattenException::create($e)
				);
			}
			return $this->redirect( $this->generateUrl('site_gallery_category', array('cRefId' => $this->gallery->category->getRefId())) );
		} else {
			// Получение choice-list альбомов, исключая те, что уже созданы
			$this->gallery->getAlbumChoiceList( $this->gallery->category );		
		}
		return $this->gallery->getOutput();
	}
	
	/**
	 * Производит подготовительные операции при создании альбома
	 * @param string $cRefId Идентификатор категории
	 * @throws NotFoundHttpException
	 */
	private function addAlbum_PreAction($cRefId) {
		$this->gallery = $this->get('gallery_service');
		if ( !$this->getRequest()->isXmlHttpRequest() )
			$this->gallery->getCategoryList( array('withCovers' => false) );
		// Проверка существования заданной категории
		$category = $this->gallery->getCategory( $cRefId );
		if ( is_null($category) )
			throw $this->createNotFoundException( sprintf('Категория %s не существует',$cRefId) );
	}
	
	/**
	 * Добавляет в категорию новый альбом
	 * @param string $cRefId
	 * @return multitype:\Symfony\Component\Form\FormView |\Symfony\Component\HttpFoundation\RedirectResponse
	 */
	private function addAlbum_DoAction($cRefId, $aRefId) {
		try {
			$em = $this->getDoctrine()->getManager();
			$dicItem = $em->getRepository('SiteCoreBundle:DictionaryItem')->getItem(null, $aRefId);
		} catch (NoResultException $e) {
			throw $this->createNotFoundException( sprintf('Значение "%s" не найдено в справочнике альбомов', $aRefId), $e );
		}
		// Проверка существования альбома с указанным типом
		$album = $this->gallery->getAlbum($cRefId, $aRefId);
		// Если нашли, то ошибка
		if ( !is_null($album) )			
			throw new \Exception(sprintf('Альбом %s уже существует', $aRefId));
		// Если не нашли, то создаём
		$this->gallery->createAlbum( $this->gallery->category, $dicItem );
	}
	
	/* ================================
	 * Add Image
	================================ */	
	
	/**
	 * Добавляет в альбом новые изображения
	 * @param string $cRefId
	 * @param string $aRefId
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|multitype:\Symfony\Component\Form\FormView |\Symfony\Component\HttpFoundation\Response
	 *
	 * @Template()
	 * @Secure(roles="ROLE_MEMBER")
	 */
	public function addImagesAction($cRefId, $aRefId) {
		$this->addImage_PreAction($cRefId, $aRefId);	
		// Создание формы отправки изображения
		$image  = new Image();
		$form = $this->createFormBuilder( $image )
			->add('file', 'file', array('required' => true))
			->getForm();
		// Получение данных, присланных через форму отправки
		$errors = null;
		if ( $this->getRequest()->getMethod() === 'POST') {
			$form->bind( $this->getRequest() );
			// Валидация данных с формы
			if ( $form->isValid() ) {
				$status = $this->addImage_DoAction( $this->gallery->album, $image );			
			} else {
				$status = GalleryService::RES_FAILURE;
// 				$errors = $form->getErrors();
				$errors = array();
// 				$form->all()->getErrors();
				foreach ( $form->all() as $subform )
					foreach ( $subform->getErrors() as $error )
						$errors[] = $error->getMessage();
			}
		}
		return array_merge($this->gallery->getOutput(), array('form' => $form->createView(), 'errors' => $errors, 'e' => $form->getErrors()));
	}
		
	/**
	 * Производит подготовительные операции при добавлении изображения
	 * @param string $cRefId Идентификатор категории
	 * @param string $aRefId Идентификатор альбома
	 * @throws AccessDeniedException
	 * @throws NotFoundHttpException
	 */
	private function addImage_PreAction($cRefId, $aRefId) {
		$this->gallery = $this->get('gallery_service');
		if ( !$this->getRequest()->isXmlHttpRequest() )
			$this->gallery->getCategoryList( array('withCovers' => false) );
		// Проверка существования заданного альбома
		$album = $this->gallery->getAlbum( $cRefId, $aRefId );
		if ( is_null($album) )
			throw $this->createNotFoundException( sprintf('Альбома %s в категории %s не существует', $aRefId, $cRefId) );
		// Проверка прав доступа
		if ( !$this->gallery->canUserAddImage( $album ) )
			throw new AccessDeniedException( 'Недостаточно прав доступа' );
		// Получение свободного пространства пользователя (только для альбомов, в которых разрешена загрузка)
		if ( $album->getAllowAdd() )
			$this->gallery->getUserSpace();
	}
	
	/**
	 * Добавляет в альбом новые изображения
	 * 
	 * @param ImageAlbum $album Альбом
	 * @param Image $image Изображение
	 * @throws \Exception
	 */
	private function addImage_DoAction(ImageAlbum $album, Image $image) {	
		// Если альбом пользовательский, то проверить дисковое пространство
		if ( $album->getAllowAdd() ) {
			if ( $this->gallery->freeSpace < $image->getFile()->getClientSize() )
				throw new \Exception('Невозможно загрузить изображение. Превышена дисковая квота.');
		} elseif ( !$this->get('security.context')->isGranted('ROLE_MODERATOR') ) {
			throw new \Exception('Недостаточно прав для загрузки изображений в указанный альбом.');
		}
		$status = $this->gallery->addImage( $album, $image->getFile() );
		if ( GalleryService::RES_FAILURE === $status ) {
			// TODO Делаем что-то в случае ошибки
		}
		return $status;
	}
}
?>