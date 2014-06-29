<?php
namespace Site\GalleryBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Site\GalleryBundle\Entity\ImageCategory;

class ImageAlbumRep extends EntityRepository
{
	/**
	 * Возвращает список альбомов в категории с обложками
	 * @param ImageCategory $category
	 */
	public function getAlbumsWithCovers( ImageCategory $category ) {
		return $this->getEntityManager()
		->createQuery('SELECT alb, albd, cover
				FROM SiteGalleryBundle:ImageAlbum alb
				LEFT JOIN alb.dictionary albd
				LEFT JOIN alb.coverImage cover
				WHERE alb.categoryId = :id
				ORDER BY alb.name ASC')
					->setParameter('id', $category->getId())
					->getResult();
	}
	
	/**
	 * Возвращает список альбомов в категории
	 * @param ImageCategory $category
	 */
	public function getAlbums( ImageCategory $category ) {
		return $this->getEntityManager()
		->createQuery('SELECT alb
				FROM SiteGalleryBundle:ImageAlbum alb
				LEFT JOIN alb.dictionary albd
				WHERE alb.categoryId = :id
				ORDER BY alb.name ASC')
					->setParameter('id', $category->getId())
					->getResult();
	}
	
	/**
	 * Возвращает альбом и его обложку
	 * @param integer $cRefId
	 * @param string $aRefId
	 * @return \Site\GalleryBundle\Entity\ImageAlbum
	 * @throws NonUniqueResultException
	 */
	public function getAlbumWithCover($cRefId, $aRefId) {
		return $this->getEntityManager()
		->createQuery('SELECT sc, cover, c, scd
				FROM SiteGalleryBundle:ImageAlbum sc
				LEFT JOIN sc.coverImage cover
				LEFT JOIN sc.dictionary scd
				LEFT JOIN sc.category c
				WHERE c.id = :id AND scd.refId = :aRefId			
				ORDER BY sci.id DESC')
					->setParameter('id', $cRefId)
					->setParameter('aRefId', $aRefId)
					->getOneOrNullResult();
	}
	
	/**
	 * Возвращает альбом
	 * @param integer $cRefId
	 * @param string $aRefId
	 * @return \Site\GalleryBundle\Entity\ImageAlbum
	 * @throws NonUniqueResultException
	 */
	public function getAlbum($cRefId, $aRefId) {
		return $this->getEntityManager()
		->createQuery('SELECT sc, c, scd
				FROM SiteGalleryBundle:ImageAlbum sc
				LEFT JOIN sc.dictionary scd
				LEFT JOIN sc.category c
				WHERE c.id = :id AND scd.refId = :aRefId')
					->setParameter('id', $cRefId)
					->setParameter('aRefId', $aRefId)
					->getOneOrNullResult();
	}
	
	/**
	 * Возвращает список всех заложенных в справочнике альбомов
	 */
	public function getAlbumsList($cRefId = null) {
		
		$list = $this->getEntityManager()
		->createQuery('SELECT alb.dictId
				FROM SiteGalleryBundle:ImageAlbum alb
				LEFT JOIN alb.category c
				WHERE c.id = :id')
		->setParameter('id', $cRefId)
		->getResult();
		return $this->getEntityManager()->getRepository('SiteCoreBundle:DictionaryList')->getDicListWithItems(null,'albums', $list);

// 		return $this->getEntityManager()
// 		->createQuery('SELECT dl, di
// 				FROM SiteCoreBundle:DictionaryList dl
// 				LEFT JOIN dl.items di
// 				WHERE dl.objId = :dlId and di.objId not in (SELECT alb.dictId
//  					FROM SiteGalleryBundle:ImageAlbum alb
//  					LEFT JOIN alb.category c
//  					WHERE c.id = :cid)
// 				')
// 		->setParameter('dlId', 'albums')
// 		->setParameter('cid', $cRefId)
// 		->getResult();
	}
	
	
// 	public function getAlbum($id) {
// 		return $this->getEntityManager()
// 		->createQuery('SELECT sc, c, scd
// 				FROM SiteGalleryBundle:ImageAlbum sc
// 				LEFT JOIN sc.dictionary scd
// 				LEFT JOIN sc.category c
// 				WHERE sc.id = :id')
// 						->setParameter('id', $id)
// 						->getSingleResult();
// 	}

}
?>