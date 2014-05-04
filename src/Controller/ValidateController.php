<?php
namespace Site\GalleryBundle\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Site\GalleryBundle\Controller\DefaultController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\File;

class ValidateController extends DefaultController {
	
	const NO_IMAGE = 'noImage';
	const NO_COVER = 'noCover';
	const NO_THUMB = 'noThumb';
	const NO_PATH = 'noPath';
	const NO_THUMB_OLD = 'noThumbOld';
	const NO_THUMB_PATH = 'noThumbPath';
	const NO_THUMB_PATH_OLD = 'noThumbPathOld';
	
	
	const ST_OK = 'OK';
	const ST_NOOK = 'NOOK';
	const ST_FIX = 'FIX';
	const ST_BUG = 'BUG';
	
	
	private function validateCategory($category, $fix = false) {
		$check = array();
		$total = self::ST_OK;
		// Проверка существования папки
		if ( !file_exists( $category->getAbsoluteDir() ) ) {
			$check[self::NO_PATH] = self::ST_BUG;
			$total = self::ST_NOOK;
			if ( $fix ) {
				// Исправление
				$category->createDir();
				$check[self::NO_PATH] = self::ST_FIX;
			}
		} else $check[self::NO_PATH] = self::ST_OK;
		// Проверка существования обложки
		if ( is_null( $category->getCoverImage() ) ) {
			$check[self::NO_COVER] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_COVER] = self::ST_OK;
		$check['total'] = $total;
		return $check;
	}
	
	private function validateAlbum($album, $fix = false) {
		$check = array();
		$total = self::ST_OK;
		// Проверка существования папки
		if ( !file_exists( $album->getAbsoluteDir() ) ) {
			$check[self::NO_PATH] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_PATH] = self::ST_OK;
		// Проверка существования папки с миниатюрами
		if ( !file_exists( $album->getAbsoluteThumbDir() ) ) {
			$check[self::NO_THUMB_PATH] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_THUMB_PATH] = self::ST_OK;
		// Проверка существования папки с миниатюрами (старая)
		if ( !file_exists( $album->getAbsoluteThumbDir() ) ) {
			$check[self::NO_THUMB_PATH_OLD] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_THUMB_PATH_OLD] = self::ST_OK;
		// Исправление
		if ( $fix && ( $check[self::NO_PATH] == self::ST_BUG 
				|| $check[self::NO_THUMB_PATH] == self::ST_BUG 
				|| $check[self::NO_THUMB_PATH_OLD] = self::ST_BUG ) ) {
			$album->createDir();
			$check[self::NO_PATH] = self::ST_FIX;
			$check[self::NO_THUMB_PATH] = self::ST_FIX;
			$check[self::NO_THUMB_PATH_OLD] = self::ST_FIX;
		}
		// Проверка существования обложки
		if ( is_null( $album->getCoverImage() ) ) {
			$check[self::NO_COVER] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_COVER] = self::ST_OK;
		$check['total'] = $total;
		return $check;
	}
	
	private function validateImage($image, $fix = false) {
		$check = array();
		$total = self::ST_OK;
		// Проверка изображения
		if ( !file_exists( $image->getAbsolutePath() ) ) {
			$check[self::NO_IMAGE] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_IMAGE] = self::ST_OK;
		// Проверка миниатюры
		if ( !file_exists( $image->getAbsoluteThumbPath() ) ) {
			$check[self::NO_THUMB] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_THUMB] = self::ST_OK;
		// Проверка миниатюры (старой)
		if ( !file_exists( $image->getAbsoluteThumbPath_old() ) ) {
			$check[self::NO_THUMB_OLD] = self::ST_BUG;
			$total = self::ST_NOOK;
		} else $check[self::NO_THUMB_OLD] = self::ST_OK;
		// Исправление
		if ( $fix && ( $check[self::NO_THUMB] == self::ST_BUG
				|| $check[self::NO_THUMB_OLD] == self::ST_BUG ) ) {
			$image->createThumbs();
			$check[self::NO_THUMB] = self::ST_FIX;
			$check[self::NO_THUMB_OLD] = self::ST_FIX;
		}
		$check['total'] = $total;
		return $check;
	}
	
	/**
	 * @Template()
	 */
	public function validateAllAction() {
		$this->initVars();	
		$this->getCategoryList();
		$this->body['categoryList'] = $this->categoryList;	
		$fix = $this->getRequest()->query->get('fix')=='fixIt' ? true : false;
		$errorCategoryList = array();
		$errorAlbumList = array();
		$errorImageList = array();
		// Проверка категорий		
		$this->getCategoryList( true );
		foreach ( $this->categoryList as $category ) {
			// Проверка категории
			$check = $this->validateCategory($category, $fix);
			if ( $check['total'] == self::ST_NOOK )
				$errorCategoryList[] = array( 'category' => $category, 'check' => $check );
			$albumList = $category->getAlbums();
			foreach ( $albumList as $album ) {
				// Проверка альбома
				$check = $this->validateAlbum($album, $fix);
				if ( $check['total'] == self::ST_NOOK )
					$errorAlbumList[] = array( 'album' => $album, 'check' => $check );
				$imageList = $album->getImages();
				foreach ( $imageList as $image ) {
					// Проверка изображения
					$check = $this->validateImage($image, $fix);
					if ( $check['total'] == self::ST_NOOK )
						$errorImageList[] = array( 'image' => $image, 'check' => $check );
				}
			}
		}
		$this->body['errorCategoryList'] = $errorCategoryList;
		$this->body['errorAlbumList'] = $errorAlbumList;
		$this->body['errorImageList'] = $errorImageList;		
		return $this->createResponse();
	}
}
?>