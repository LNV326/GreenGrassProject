<?php
namespace Site\GalleryBundle;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Site\GalleryBundle\Entity\ImageCategory;

class GalleryService {
	
	protected $em;
	
	// Коэффициенты расчёта доступного пользователю дискового пространства
	protected $K_POSTS_MIN = 0; // Минимальное число тематических сообщений
	protected $K_POSTS = 1; // Инкремент тематических сообщений
	protected $K_KB = 0; // + Кб за каждые K_POSTS тематических сообщений
	
	public $categoryList = null;
	public $category = null;
	public $album = null;
	public $images = null;
	
	public function __construct(EntityManager $em, $posts_min, $posts_inc, $kb_inc) {
		$this->em = $em;		
		$this->K_POSTS_MIN = $posts_min;
		$this->K_POSTS = $posts_inc;
		$this->K_KB = $kb_inc;
	}
	
	/**
	 * Возвращает список категорий
	 * @param boolean $withCovers Подгрузить обложки категорий
	 */
	public function getCategoryList($withCovers = false) {
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageCategory');
		if ( $withCovers )
			$this->categoryList = $repo->getCatsWithCovers();
		else $this->categoryList = $repo->getCats();
		return $this->categoryList;
	}
	
	/**
	 * Возвращает категорию
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param boolean $withAlbums Подгрузить список альбомов
	 * @param boolean $withCovers Подгрузить обложки альбомов
	 * @throws \Exception
	 * @throws NoResultException
	 */
	public function getCategory($cRefId, $withAlbums = false, $withCovers = false) {
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageCategory');
		if ( $withAlbums ) {
			if ( $withCovers )
				$this->category = $repo->getCatWithAlbumsWithCovers($id);
			else throw new \Exception('Функция не реализована');
		} else {
			$this->category = $repo->find($id);
			if ( is_null($this->category) )
				throw new NoResultException(sprintf('Изображение %s не существует', $id));
		}
		return $this->category;
	}
	
	/**
	 * Возвращает альбом (с изображениями)
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param string $aRefId Текстовый идентификатор альбома (уникален в пределах категории)
	 * @param boolean $withImages Подгрузить изображения
	 * @throws \Exception
	 */
	public function getAlbum($cRefId, $aRefId, $withImages = false) {
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации
		$repo = $this->em->getRepository('SiteGalleryBundle:ImageAlbum');
		if ( $withImages )
			$this->album = $repo->getAlbumWithImages($id, $aRefId);
		else $this->album = $repo->getAlbum($id, $aRefId);
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
	public function getImage($iId, $withAlbum = false, $withCategory = false) {
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		if ( $withAlbum ) {
			if ( $withCategory )
				throw new \Exception('Функция не реализована');
			else throw new \Exception('Функция не реализована');
		} else {
			$this->image = $repo->find($iId);
			if ( is_null($this->image) )
				throw new NoResultException(sprintf('Изображение %s не существует', $iId));
		}
		return $this->image;
	}
	
	/**
	 * Возвращает изображения пользователя
	 * @param unknown $uId
	 * @param string $withAlbum
	 * @param string $withCategory
	 * @throws \Exception
	 */
	public function getUserImages($uId, $withAlbum = false, $withCategory = false) {
		$repo = $this->em->getRepository('SiteGalleryBundle:Image');
		if ( $withAlbum ) {
			if ( $withCategory )
				throw new \Exception('Функция не реализована');
			else $this->images = $repo->getUserImages( $uId );
		} else {
			$this->images = $repo->getUserImages( $uId );
		}
		return $this->images;
	}
	
	/**
	 * Возвращает доступное пользователю дисковое пространство
	 * Исключаются из расчёта изображения, загруженные админами в закрытые альбомы
	 * @return number
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
				try {
					$this->occupSpace += filesize( $image->getAbsolutePath() );
				} catch (\Exception $e) {}
			}
			$this->freeSpace = $this->totalSpace - $this->occupSpace;
		}
		return $this->freeSpace;
	}
}
?>