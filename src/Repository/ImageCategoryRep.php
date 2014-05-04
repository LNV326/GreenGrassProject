<?php
namespace Site\GalleryBundle\Repository;

use Site\GalleryBundle\Entity\ImageCategory;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class ImageCategoryRep extends EntityRepository {
	/**
	 * Возвращает список категорий изображений
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getCats() {
		return new ArrayCollection(
				$this->getEntityManager()
						->createQuery(
								'SELECT c
						FROM SiteGalleryBundle:ImageCategory c
						ORDER BY c.position ASC')->getResult());
	}

	/**
	 * Возвращает список категорий изображений с обложками
	 * TODO Джойнит альбомы, т.к для изображений нужен URL, который цепляется от альбомов
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getCatsWithCovers() {
		return new ArrayCollection(
				$this->getEntityManager()
						->createQuery(
								'SELECT c, sc, ci
						FROM SiteGalleryBundle:ImageCategory c
						LEFT JOIN c.albums sc
						LEFT JOIN c.coverImage ci
						ORDER BY c.position ASC')->getResult());
	}

	/**
	 * Возвращает категорию и список альбомов в ней с обложками
	 * @param integer $cRefid
	 * @return \Site\GalleryBundle\Entity\ImageCategory
	 */
	public function getCatWithAlbumsWithCovers($cRefid) {
		return $this->getEntityManager()
				->createQuery(
						'SELECT c, sc, sci, scd
				FROM SiteGalleryBundle:ImageCategory c
				LEFT JOIN c.albums sc
				LEFT JOIN sc.coverImage sci
				LEFT JOIN sc.dictionary scd
				WHERE c.id = :id
				ORDER BY sc.name ASC')->setParameter('id', $cRefid)
				->getSingleResult();
	}
}
?>