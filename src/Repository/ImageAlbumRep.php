<?php
namespace Site\GalleryBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ImageAlbumRep extends EntityRepository
{
	/**
	 * Возвращает альбом и изображения в нём
	 * @param integer $cRefId
	 * @param string $aRefId
	 * @return \Site\GalleryBundle\Entity\ImageAlbum
	 */
	public function getAlbumWithImages($cRefId, $aRefId) {
		return $this->getEntityManager()
		->createQuery('SELECT sc, sci, c, scd
				FROM SiteGalleryBundle:ImageAlbum sc
				LEFT JOIN sc.images sci
				LEFT JOIN sc.dictionary scd
				LEFT JOIN sc.category c
				WHERE c.id = :id AND scd.refId = :aRefId
				ORDER BY sci.id DESC')
					->setParameter('id', $cRefId)
					->setParameter('aRefId', $aRefId)
					->getSingleResult();
	}
	
	/**
	 * Возвращает альбом
	 * @param integer $cRefId
	 * @param string $aRefId
	 * @return \Site\GalleryBundle\Entity\ImageAlbum
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
					->getSingleResult();
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
	
	
	public function getAlbumById($id) {
		return $this->getEntityManager()
		->createQuery('SELECT sc, c, scd
				FROM SiteGalleryBundle:ImageAlbum sc
				LEFT JOIN sc.dictionary scd
				LEFT JOIN sc.category c
				WHERE sc.id = :id')
						->setParameter('id', $id)
						->getSingleResult();
	}
}
?>