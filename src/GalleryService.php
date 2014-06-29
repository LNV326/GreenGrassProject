<?php
namespace Site\GalleryBundle;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

use Site\GalleryBundle\Entity\ImageCategory;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\GalleryBundle\Entity\Image;
use Site\CoreBundle\Entity\UserConfigInfo;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class GalleryService {
	
	protected $em;
	
	// Коэффициенты расчёта доступного пользователю дискового пространства
	protected $K_POSTS_MIN = 0; // Минимальное число тематических сообщений
	protected $K_POSTS = 1; // Инкремент тематических сообщений
	protected $K_KB = 0; // + Кб за каждые K_POSTS тематических сообщений
	
	// Настройки страницы альбома
	public $IMGS_ON_PAGE = 40; // Число изображений на страница альбома
	
	public $categoryList = null;
	public $category = null;
	public $albumList = null;
	public $album = null;
	public $imageList = null;
	public $image = null;
	
	public function __construct(EntityManager $em, $posts_min, $posts_inc, $kb_inc) {
		$this->em = $em;	
		$this->K_POSTS_MIN = $posts_min;
		$this->K_POSTS = $posts_inc;
		$this->K_KB = $kb_inc;
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
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		$this->imageList = $repo->getImageList( $album );
		return $this->imageList;
	}
	
	/**
	 * Возвращает категорию
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param boolean $withAlbums Подгрузить список альбомов
	 * @param boolean $withCovers Подгрузить обложки альбомов
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function getCategory( $cRefId, $params = array() ) {
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageCategory');
		// Can throw NonUniqueResultException
		if ( $params['withCovers'] == true )
			$this->category = $repo->getCategoryWithCover( $id );
		else $this->category = $repo->getCategory( $id );
		// Валидация только на существование запрошенного объекта, т.е на категорию
		if ( is_null($this->category) )
		 	throw new NoResultException(sprintf('Категория %s не существует', $id));
		if ( $params['withAlbums'] == true )
			$this->getAlbumsList( $this->category, $params );
		return $this->category;
	}
	
	/**
	 * Возвращает альбом (с изображениями)
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param string $aRefId Текстовый идентификатор альбома (уникален в пределах категории)
	 * @param boolean $withImages Подгрузить изображения
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function getAlbum( $cRefId, $aRefId, $params = array() ) {
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageAlbum');
		// Can throw NonUniqueResultException
		if ( $params['withCovers'] == true )
			$this->album = $repo->getAlbumWithCover($id, $aRefId);
		else $this->album = $repo->getAlbum($id, $aRefId);
		// Валидация только на существование запрошенного объекта, т.е на категорию
		if ( is_null($this->album) )
			throw new NoResultException(sprintf('Альбом %s не существует', $aRefId));
		$this->category = $this->album->getCategory();
		if ( $params['withImages'] == true )
			$this->getImagesList( $this->album, $params);
		return $this->album;
	}
	
	/**
	 * Возвращает изображение
	 * @param integer $iId Идентификатор изображения
	 * @param boolean $withAlbum Подгрузить альбом
	 * @param boolean $withCategory Подгрузить категорию
	 * @throws \Exception
	 * @throws \NoResultException
	 */
	public function getImage($iId, $params = array() ) {
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		// Can throw NonUniqueResultException
		$this->image = $repo->getImage( $iId );
		// Валидация только на существование запрошенного объекта, т.е на категорию
		if ( is_null($this->image) )
			throw new NoResultException(sprintf('Изображение %s не существует', $iId));
		$this->album = $this->image->getAlbum();
		$this->category = $this->album->getCategory();
		return $this->image;
	}
	
	/**
	 * Возвращает изображения пользователя
	 * @param unknown $uId
	 * @param string $withAlbum
	 * @param string $withCategory
	 * @throws \Exception
	 */
	public function getUserImageList( UserConfigInfo $user, $params = array()) {
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		$this->imageList = $repo->getUserImages( $user );
		return $this->imageList;
	}
	
	/* ================================
	 * AddController
	   ================================ */
	
	/**
	 * Возвращает доступное пользователю дисковое пространство
	 * Исключаются из расчёта изображения, загруженные админами в закрытые альбомы
	 * @return number
	 * @throws \Exception
	 */
	protected function getUserSpace() {
		$posts = $this->getUser()->getPostsCount() - $this->getUser()->getPostsBadCount();
		if ( $posts >= self::K_POSTS_MIN ) {
			$inc = ceil( $posts/self::K_POSTS );
			$this->totalSpace = $inc * self::K_KB * 1024; // Расчёт максимального доступного пространства к байтах
			// Расчёт занятого пространства
			$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:Image');
			$images = $repo->getUserImages( $this->getUser()->getId() );
			foreach ($images as $image) {
				$this->occupSpace += filesize( $image->getAbsolutePath() );
			}
			$this->freeSpace = $this->totalSpace - $this->occupSpace;
		}
		return $this->freeSpace;
	}
	
	protected function addImage(UserConfigInfo $user, ImageAlbum $album, UploadedFile $file) {
		$count = 0;
		$image = new Image($count++);
		$image->setMemberId( $user->getId() )
			->setMemberName( $user->getUsername() )
			->setAlbum( $album )
			->setVisibility( 'hide' )
			->setFile( $file );
		return $image;
	}
}
?>