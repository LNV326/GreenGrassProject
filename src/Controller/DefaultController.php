<?php
namespace Site\GalleryBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;

use Site\GalleryBundle\Entity\ImageCategory;

class DefaultController extends Controller {
	// Возможные результаты работы функции
	const ST_SUCCESS = 'Success';
	const ST_FAIL = 'Fail';
	
	// Коэффициенты расчёта доступного пользователю дискового пространства
	const K_POSTS_MIN = 10; // Минимальное число тематических сообщений
	const K_POSTS = 250; // Инкремент тематических сообщений
	const K_KB = 750; // + Кб за каждые K_POSTS тематических сообщений
	
	protected $imageHostName = null;
	protected $categoryList = null;
	protected $category = null;
	protected $album = null;
	protected $images = null;
	
	protected $freeSpace = 0;
	protected $occupSpace = 0;
	protected $totalSpace = 0;
	
	// Параметры, содержащие конечный ответ
	protected $action = null;
	protected $status = null;
	protected $body = array();
	protected $error = array();
	
	public function initVars() {
		$this->imageHostName = $this->container->getParameter('img_host');
		
// 		if ( !$this->getRequest()->isXmlHttpRequest() ) {
// 			$this->getCategoryList(false);
// 		}
	}
	
	protected function createResponse() {
		return array(
				'action' => $this->action,
				'status' => count($this->error)==0 ? self::ST_SUCCESS : self::ST_FAIL,
				'body' => $this->body,
				'error' => $this->error
		);
	}
	
	/**
	 * Возвращает список категорий
	 * @param boolean $withCovers Подгрузить обложки категорий
	 */
	protected function getCategoryList($withCovers = false) {
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:ImageCategory');
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
	 */
	protected function getCategory($cRefId, $withAlbums = false, $withCovers = false) {
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации		
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:ImageCategory');
		if ( $withAlbums ) {
			if ( $withCovers )
				$this->category = $repo->getCatWithAlbumsWithCovers($id);
			else throw new \Exception('Функция не реализована');
		} else {
			$this->category = $repo->find($id);
			if ( is_null($this->category) )
				throw new NoResultException(sprintf('Изображение %s не существует', $iId));
		}
		return $this->category;			
	}
	
	/**
	 * Возвращает альбом
	 * @param string $cRefId Текстовый идентификатор категории
	 * @param string $aRefId Текстовый идентификатор альбома (уникален в пределах категории)
	 * @param boolean $withImages Подгрузить изображения
	 * @throws \Exception
	 */
	protected function getAlbum($cRefId, $aRefId, $withImages = false) {
		is_numeric($cRefId) ? $id = $cRefId : $id = ImageCategory::getIdFromRefId($cRefId); // TODO маппинг refid в objid, так уж получилось в текущей реализации		
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:ImageAlbum');
		if ( $withImages )
			$this->album = $repo->getAlbumWithImages($id, $aRefId);
		else $this->album = $repo->getAlbum($id, $aRefId);
		return $this->album;
	}
	
	/**
	 * 
	 * @param integer $iId Идентификатор изображения
	 * @param boolean $withAlbum Подгрузить альбом
	 * @param boolean $withCategory Подгрузить категорию
	 * @throws \Exception
	 * @throws \NoResultException
	 */
	protected function getImage($iId, $withAlbum = false, $withCategory = false) {
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:Image');
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
	
	protected function getUserImages($uId, $withAlbum = false, $withCategory = false) {
		$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:Image');
		if ( $withAlbum ) {
			if ( $withCategory )
				throw new \Exception('Функция не реализована');
			else $this->images = $repo->getUserImages( $uId );
		} else {
			$this->images = $repo->getUserImages( $uId );
		}
		return $this->images;
	}
}
?>