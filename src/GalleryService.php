<?php
namespace Site\GalleryBundle;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\GalleryBundle\Entity\Image;
use Site\CoreBundle\Entity\UserConfigInfo;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Site\CoreBundle\Entity\DictionaryItem;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Validator;

class GalleryService {
	
	const RES_SUCCESS = 'Success';
	const RES_FAILURE = 'Failure';
	
	protected $em;
	protected $logger;
	protected $securityContext;
	protected $validator;
	
	// Коэффициенты расчёта доступного пользователю дискового пространства
	protected $K_POSTS_MIN = 0; // Минимальное число тематических сообщений
	protected $K_POSTS = 1; // Инкремент тематических сообщений
	protected $K_KB = 0; // + Кб за каждые K_POSTS тематических сообщений
	
	// Настройки страницы альбома
	public $IMGS_ON_PAGE = 40; // Число изображений на страница альбома
	
	public $debugMode = false;
	
	public $imgHostName = '';
	
	public $categoryList = null;
	public $category = null;
	public $albumList = null;
	public $album = null;
	public $imageList = null;
	public $image = null;
	public $validationErrors = null;
	public $albumChoiceList = null;
	
	public $freeSpace = 0;
	public $totalSpace = 0;
	public $occupSpace = 0;
	
	public static $paramsDefault = array(
		'withCovers' => false,
		'withAlbums' => false,
		'withImages' => false
	);
	
	public function __construct(EntityManager $em, Logger $logger, SecurityContext $securityContext, Validator $validator, $imgHostName, $posts_min, $posts_inc, $kb_inc) {
		$this->em = $em;	
		$this->logger = $logger;
		$this->securityContext = $securityContext;
		$this->validator = $validator;
		$this->K_POSTS_MIN = $posts_min;
		$this->K_POSTS = $posts_inc;
		$this->K_KB = $kb_inc;
		$this->imgHostName = $imgHostName;
	}
	
	/* ================================
	 * ShowController
	   ================================ */
	
	/**
	 * Возвращает список категорий
	 * @param boolean $withCovers Подгрузить обложки категорий
	 * @param array $params Параметры выборки
	 */
	public function getCategoryList( $params = array() ) {
		$params = array_merge( self::$paramsDefault, $params );
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageCategory');
		if ( $params['withCovers'] == true )
			$this->categoryList = $repo->getCatsWithCovers();
		else $this->categoryList = $repo->getCats();
		return $this->categoryList;
	}
	
	/**
	 * Возвращает список альбомов в категории
	 * @param ImageCategory $category Категория
	 * @param array $params Параметры выборки
	 */
	public function getAlbumsList( ImageCategory $category, $params = array() ) {
		$params = array_merge( self::$paramsDefault, $params );
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageAlbum');
		if ( $params['withCovers'] == true )
			$this->albumList = $repo->getAlbumsWithCovers( $category );
		else $this->albumList = $repo->getAlbums( $category );
		return $this->albumList;
	}
	
	/**
	 * Возвращает список изображений в альбоме
	 * @param ImageAlbum $album Альбом
	 * @param array $params Параметры выборки
	 */
	public function getImagesList( ImageAlbum $album, $params = array() ) {
		$params = array_merge( self::$paramsDefault, $params );
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		$this->imageList = $repo->getImageList( $album );
		return $this->imageList;
	}
	
	/**
	 * Возвращает категорию
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param array $params Параметры выборки
	 * @return ImageCategory | NULL
	 * @throws NonUniqueResultException
	 */
	public function getCategory( $cRefId, $params = array() ) {
		$params = array_merge( self::$paramsDefault, $params );
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageCategory');
		// Can throw NonUniqueResultException
		if ( $params['withCovers'] == true )
			$this->category = $repo->getCategoryWithCover( $id );
		else $this->category = $repo->getCategory( $id );
		// Валидация только на существование запрошенного объекта, т.е на категорию
// 		if ( is_null($this->category) )
// 		 	throw new NoResultException(sprintf('Категория %s не существует', $id));
		if ( !is_null($this->category) )
			if ( $params['withAlbums'] == true )
				$this->getAlbumsList( $this->category, $params );
		return $this->category;
	}
	
	/**
	 * Возвращает альбом (с изображениями)
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param string $aRefId Текстовый идентификатор альбома (уникален в пределах категории)
	 * @param array $params Параметры выборки
	 * @return ImageAlbum | NULL
	 * @throws NonUniqueResultException
	 */
	public function getAlbum( $cRefId, $aRefId, $params = array() ) {
		$params = array_merge( self::$paramsDefault, $params );
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageAlbum');
		// Can throw NonUniqueResultException
		if ( $params['withCovers'] == true )
			$this->album = $repo->getAlbumWithCover($id, $aRefId);
		else $this->album = $repo->getAlbum($id, $aRefId);
		// Валидация только на существование запрошенного объекта, т.е на категорию
// 		if ( is_null($this->album) )
// 			throw new NoResultException(sprintf('Альбом %s не существует', $aRefId));
		if ( !is_null($this->album) ) {
			$this->category = $this->album->getCategory();
			if ( $params['withImages'] == true )
				$this->getImagesList( $this->album, $params);
		}
		return $this->album;
	}
	
	/**
	 * Возвращает изображение
	 * @param integer $iId Идентификатор изображения
	 * @param array $params Параметры выборки
	 * @return Image | NULL
	 * @throws NonUniqueResultException
	 */
	public function getImage($iId, $params = array() ) {
		$params = array_merge( self::$paramsDefault, $params );
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		// Can throw NonUniqueResultException
		$this->image = $repo->getImage( $iId );
		// Валидация только на существование запрошенного объекта, т.е на категорию
// 		if ( is_null($this->image) )
// 			throw new NoResultException(sprintf('Изображение %s не существует', $iId));
		if ( !is_null($this->image) ) {
			$this->album = $this->image->getAlbum();
			$this->category = $this->album->getCategory();
		}
		return $this->image;
	}
	
	/**
	 * Возвращает изображения пользователя
	 * @param UserConfigInfo $user Пользователь
	 * @param array $params Параметры выборки
	 * @throws \Exception
	 */
	public function getUserImageList( UserConfigInfo $user, $params = array()) {
		$params = array_merge( self::$paramsDefault, $params );
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		$this->imageList = $repo->getUserImages( $user );		
		return $this->imageList;
	}
	
	/* ================================
	 * AddController
	   ================================ */
	
	/**
	 * Возвращает список возможных к созданию альбомов в указанной категории
	 * @param ImageCategory $category Категория
	 * @return DictionaryList | null
	 */
	public function getAlbumChoiceList( ImageCategory $category ) {
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageAlbum');
		$this->albumChoiceList = $repo->getAlbumChoiceList( $category );
		return $this->albumChoiceList;
		// TODO необходимо определиться, возвращает ли этот метод exception
	}
	
	/**
	 * Создаёт альбом заданного типа в заданной категории и возвращает его
	 * @param ImageCategory $category Категория, в которой необходимо создать альбом
	 * @param DictionaryItem $dicItem Тип альбома
	 * @return \Site\GalleryBundle\Entity\ImageAlbum
	 * @throws \Exception
	 */
	public function createAlbum( ImageCategory $category, DictionaryItem $dicItem ) {
		$user = $this->securityContext->getToken()->getUser();
		$this->logger->warn(sprintf('%s (%d) пытается создать альбом "%s" в категории "%s"', $user->getUsername(), $user->getId(), $dicItem->getRefId(), $category->getRefId()) );
		// Создаём альбом
		$album = new ImageAlbum();
		$album->setCategory( $category )
			->setDictionary( $dicItem )
			->setName( $dicItem->getTitle() )
			->setDirName( $dicItem->getRefId() )
			->setAllowAdd( false );
		// Добавление в очередь на загрузку в БД
		try {
			$this->em->persist( $album );
			if ( !$this->debugMode )
				$this->em->flush();
		} catch (\Exception $e) {
			$errMess = sprintf('Ошибка при создании альбома пользователем %s (%d) - %s', $user->getUsername(), $user->getId(), $e->getMessage());
			$this->logger->error( $errMess );
			throw new \Exception( $errMess, 0, $e);
		}
		$this->logger->info( sprintf('Альбом "%s" в категории "%s" успешно создан пользователем %s (%d)',
				$album->getDictionary()->getRefId(),
				$category->getRefId(),
				$user->getUsername(),
				$user->getId()
		));
		$this->album = $album;
		return $this->album;
	}
	
	public function canUserAddImage( ImageAlbum $album ) {
		if ( $album->getAllowAdd() && $this->securityContext->isGranted('ROLE_MEMBER') )
			return true;
		if ( $this->securityContext->isGranted('ROLE_MODERATOR') || $this->securityContext->isGranted('ROLE_GAL_IMGS_ADD') ) {
			$this->freeSpace = 100;
			return true;
		}
		return false;
	}
	
	/**
	 * Возвращает доступное пользователю дисковое пространство
	 * Исключаются из расчёта изображения, загруженные админами в закрытые альбомы
	 * @return number
	 * @throws \Exception
	 */
	public function getUserSpace() {
		$user = $this->securityContext->getToken()->getUser();
		$posts = $user->getPostsCount() - $user->getPostsBadCount();
		if ( $posts >= $this->K_POSTS_MIN ) {
			$inc = ceil( $posts/$this->K_POSTS );
			$this->totalSpace = $inc * $this->K_KB * 1024; // Расчёт максимального доступного пространства к байтах
			// Расчёт занятого пространства
			$repo = $this->em->getRepository('SiteGalleryBundle:Image');
			$images = $repo->getUserImages( $user );
			foreach ($images as $image) {
				$this->occupSpace += filesize( $image->getAbsolutePath() );
			}
			$this->freeSpace = $this->totalSpace - $this->occupSpace;
		}
		return $this->freeSpace;
	}
	
	/**
	 * Добавляет новое изображение в заданную галерею
	 * @param ImageAlbum $album Альбом
	 * @param UploadedFile $file Загруженные файл
	 * @throws \Exception
	 * @return array|\Site\GalleryBundle\Entity\Image
	 */
	public function addImage(ImageAlbum $album, UploadedFile $file) {
		$user = $this->securityContext->getToken()->getUser();
		// Если альбом пользовательский, то изображение невидимо
		if ( $album->getAllowAdd() )
			$visibility = 0;
		elseif ( $this->securityContext->isGranted('ROLE_MODERATOR') )
			$visibility = 1;
		else
			// Ну, такого быть не должно...
			throw new \Exception('Unknown error occurred when defining visibility');
		$this->logger->warn(sprintf('%s (%d) пытается добавить изображение в альбом "%s" в категории "%s"', $user->getUsername(), $user->getId(), $album->getName(), $album->getCategory()->getName()) );
		$count = 0;
		$image = new Image($count++);
		$image->setMemberId( $user->getId() )
			->setMemberName( $user->getUsername() )
			->setAlbum( $album )
			->setVisibility( $visibility )
			->setFile( $file );
		// Добавление в очередь на загрузку в БД
		$this->validationErrors = $this->validator->validate( $image );
		if ( count( $this->validationErrors ) != 0 )
			return self::RES_FAILURE;
		try {
			$this->em->persist( $image );
// 			// Если у альбома нет обложки, сделать текущее изображение обложкой
// 			if ( is_null($album->getCoverImage()) )
// 				$album->setCoverImage( $image );
			if ( !$this->debugMode )
				$this->em->flush();			
		} catch (\Exception $e) {
			$errMess = sprintf('Ошибка при добавлении изображения пользователем %s (%d) - %s', $user->getUsername(), $user->getId(), $e->getMessage());
			$this->logger->error( $errMess );
			throw new \Exception( $errMess, 0, $e);
		}
		$this->logger->info(sprintf('Новое изображение успешно добавлено в альбом "%s" в категории "%s" пользователем %s (%d)', $this->album->getDictionary()->getRefId(), $this->album->getCategory()->getRefId(), $user->getUsername(), $user->getId()) );
		$this->image = $image;	
		return self::RES_SUCCESS;
	}
	
	/* ================================
	 * EditController
	================================ */
	
	/**
	 * Устанавливает изображение обложкой альбома
	 * @param ImageAlbum $album Альбом
	 * @param Image $image Изображение
	 * @throws \Exception
	 */
	public function setAlbumCover(ImageAlbum $album, Image $image) {
		$user = $this->securityContext->getToken()->getUser();
		$this->logger->warn(sprintf('%s (%d) пытается установить изображение id="%d" как обложку альбома "%s" в категории "%s"', $user->getUsername(), $user->getId(), $image->getId(), $album->getDictionary()->getRefId(), $album->getCategory()->getRefId()) );			
		$album->setCoverImage( $image );
		try {
			if ( !$this->debugMode )
				$this->em->flush();
			$this->logger->info(sprintf('Изображение id="%d" успешно установлено обложкой альбома "%s" в категории "%s" пользователем %s (%d)', $image->getId(), $album->getDictionary()->getRefId(), $album->getCategory()->getRefId(), $user->getUsername(), $user->getId()) );
		} catch (\Exceprion $e) {
			$errMess = sprintf('Ошибка при установке изображения id="%d" обложкой альбома "%s" в категории "%s" пользователем %s (%d) - %s', $image->getId(), $album->getDictionary()->getRefId(), $album->getCategory()->getRefId(), $user->getUsername(), $user->getId(), $e->getMessage());
			$this->logger->error( $errMess );
			throw new \Exception( $errMess, 0, $e);
		}
	}
	
	/**
	 * Устанавливает изображение обложкой категории
	 * @param ImageCategory $category Категория
	 * @param Image $image Изображение
	 * @throws \Exception
	 */
	public function setCategoryCover(ImageCategory $category, Image $image) {
		$user = $this->securityContext->getToken()->getUser();
		$this->logger->warn(sprintf('%s (%d) пытается установить изображение id="%d" как обложку категории "%s"', $user->getUsername(), $user->getId(), $image->getId(), $category->getRefId()) );			
		$category->setCoverImage( $image );
		try {
			if ( !$this->debugMode )
				$this->em->flush();
			$this->logger->info(sprintf('Изображение id="%d" успешно установлено обложкой категории "%s" пользователем %s (%d)', $image->getId(), $category->getRefId(), $user->getUsername(), $user->getId()) );
		} catch (\Exceprion $e) {
			$errMess = sprintf('Ошибка при установке изображения id="%d" обложкой категории "%s" пользователем %s (%d) - %s', $image->getId(), $category->getRefId(), $user->getUsername(), $user->getId(), $e->getMessage());
			$this->logger->error( $errMess );
			throw new \Exception( $errMess, 0, $e);
		}
	}
	
	/**
	 * Устанавливает видимость изображения
	 * @param Image $image Изображение
	 * @param unknown $visible Видимость (1 - видим, 0 - невидим)
	 * @throws \Exception
	 */
	public function setImageVisibility(Image $image, $visible) {
		$user = $this->securityContext->getToken()->getUser();
		$this->logger->warn(sprintf('%s (%d) пытается изменить видимость изображения id="%d"', $user->getUsername(), $user->getId(), $image->getId()) );
		$image->setVisibility( $visible );
		try {
			if ( !$this->debugMode )
				$this->em->flush();
			$this->logger->info(sprintf('У изображения id="%d" успешно изменена видимость пользователем %s (%d)', $image->getId(), $user->getUsername(), $user->getId()) );
		} catch (\Exceprion $e) {
			$errMess = sprintf('Ошибка при изменении видимости изображения id="%d" пользователем %s (%d) - %s', $image->getId(), $user->getUsername(), $user->getId(), $e->getMessage() );	
			$this->logger->error( $errMess );
			throw new \Exception( $errMess, 0, $e);
		}
	}
	
	/* ================================
	 * RemoveController
	================================ */
	
	public function deleteImage(Image $image) {
		$user = $this->securityContext->getToken()->getUser();
		$this->logger->warn(sprintf('%s (%d) пытается удалить изображение id="%d" - владелец %s (%d)', $user->getUsername(), $user->getId(), $image->getId(), $image->getMemberName(), $image->getMemberId()) );
		try {
			$this->em->remove( $image );
			if ( !$this->debugMode )
				$this->em->flush();
			$this->logger->info(sprintf('Изображение успешно удалено пользователем %s (%d)', $image->getId(), $user->getUsername(), $user->getId()) );
		} catch (\Exceprion $e) {
			$errMess = sprintf('Ошибка при удалении изображения id="%d" пользователем %s (%d) - %s', $image->getId(), $user->getUsername(), $user->getId(), $e->getMessage() );
			$this->logger->error( $errMess );
			throw new \Exception( $errMess, 0, $e);
		}
	}
	
	/* ================================
	 * Other
	================================ */
	
	public function getOutput() {
		 return array(
			'categoryList' => $this->categoryList,
			'category' => $this->category,
		 	'albumList' => $this->albumList,
			'album' => $this->album,
		 	'imageList' => $this->imageList,
		 	'image' => $this->image,
			'imgHostName' => $this->imgHostName,
			'validationErrors' => $this->validationErrors,
		 	'albumChoiceList' => $this->albumChoiceList,
		 	'spaceInfo' => array(
				'total' => $this->totalSpace,
				'free' => $this->freeSpace,
				'occup' => $this->occupSpace
		 	)
		);
	}
	
	public function getAlbumAvalibleActionsList(ImageAlbum $album) {
		$perms = array(
			'ADD' => false,
			'EDIT' => false,
			'DEL' => false
		);
		// Добавление изображений
		if (( $album->getAllowAdd() && $this->securityContext->isGranted('ROLE_MEMBER') )
			|| $this->securityContext->isGranted('ROLE_MODERATOR')
			|| $this->securityContext->isGranted('ROLE_GAL_IMGS_ADD') )
			$perms['ADD'] = true;		
		// Редактирование альбома
		if ( $this->securityContext->isGranted('ROLE_MODERATOR')
			|| $this->securityContext->isGranted('ROLE_GAL_ALB_EDIT'))
			$perms['EDIT'] = true;
		// Удаление альбома
		if ( $this->securityContext->isGranted('ROLE_ADMIN')
			|| $this->securityContext->isGranted('ROLE_GAL_ALB_DEL'))
			$perms['DEL'] = true;
		return $perms;
	}
}
?>