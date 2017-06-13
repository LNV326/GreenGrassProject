<?php
namespace Site\GalleryBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Site\GalleryBundle\Entity\ImageAlbum;
use Site\CoreBundle\Entity\UserConfigInfo;

class ImageRep extends EntityRepository
{
	public function getImageList(ImageAlbum $album) {
		return $this->getEntityManager()
		->createQuery('SELECT i
				FROM SiteGalleryBundle:Image i
				WHERE i.albumId = :id
				ORDER BY i.id DESC')
						->setParameter('id', $album->getId())
// 						->setFirstResult($offset)
// 						->setMaxResults($count)
						->getResult();
	}
	
	public function getImage( $iId ) {
		return $this->find($iId);
	}
	
	
	/**
	 * ���������� ������ ��������� ����������� �����������
	 * @param integer $count
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function findLastImages( $count)
	{
		return $this->getEntityManager()
		->createQuery('SELECT i
				FROM SiteGalleryBundle:Image i
				ORDER BY i.id DESC')
					->setMaxResults($count)
					->getResult();
	}
	
	/**
	 * ���������� ������ ��������� ����������� ����������� � ���������
	 * @param integer $id
	 * @param integer $count
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function findLastImagesInCatByCatId($id, $count)
	{
		return $this->getEntityManager()
		->createQuery('SELECT i
				FROM SiteGalleryBundle:Image i
				WHERE i.subCategoryId IN (SELECT sc.id FROM SiteGalleryBundle:ImageAlbum sc WHERE sc.categoryId = :id)
				ORDER BY i.id DESC')
				->setParameter('id', $id)
				->setMaxResults($count)
				->getResult();
	}
	
	/**
	 * ��������� ������ ����������� �� ������ �� id
	 * @param array $ids
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function findImagesById($ids) {
		return $this->getEntityManager()
		->createQuery('SELECT i
				FROM SiteGalleryBundle:Image i
				WHERE i.id IN (:ids)')
						->setParameter('ids', $ids)
						->getResult();
	}
	
	/**
	 * ���������� ������ ��������� ����������� ����������� � ������������
	 * @param integer $id
	 * @param integer $count
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function findLastImagesInSubCatBySubCatId($id, $offset, $count)
	{
		return $this->getEntityManager()
		->createQuery('SELECT i
				FROM SiteGalleryBundle:Image i
				WHERE i.subCategoryId = :id
				ORDER BY i.id DESC')
					->setParameter('id', $id)
					->setFirstResult($offset)
					->setMaxResults($count)
					->getResult();
	}
	
	/**
	 * ������������� ������ ������ ��� ������ �����������
	 * ��������� �������: show (����������), hide (��������), trash (������������ � ��������)
	 * @param array|integer $ids
	 * @param string $status
	 */
	public function setStatusImages($ids, $status) {
		return $this->getEntityManager()
		->createQuery('UPDATE SiteGalleryBundle:Image i SET i.status = :status
				WHERE i.id IN (:ids)')
					->setParameter('ids', $ids)
					->setParameter('status', $status)
					->getResult();
	}
	
	/**
	 * ���������� ������ �����������, ����������� � �������
	 * @param \Doctrine\Common\Collections\Collection $ids
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getTrashImagesByIDs($ids) {
		return $this->getEntityManager()
		->createQuery("SELECT i
					FROM SiteGalleryBundle:Image i
					WHERE i.status = 'trash' AND i.id IN (:ids)")
						->setParameter('ids', $ids)
						->getResult();
	}
	
	/**
	 * ������������� ����� ID ������� ��� ������ �����������
	 * @param array|integer $ids
	 * @param integer $toAlbumId
	 */
	public function moveImages($ids, $toAlbumId) {	
		return $this->getEntityManager()
		->createQuery('UPDATE SiteGalleryBundle:Image i SET i.albumId = :toAlbumId
				WHERE i.id IN (:ids)')
					->setParameter('ids', $ids)
					->setParameter('toAlbumId', $toAlbumId)
					->getResult();
		
	}
	
	
	public function getUserImages( UserConfigInfo $user ) {
		return $this->getEntityManager()
		->createQuery('SELECT i, a
				FROM SiteGalleryBundle:Image i
				LEFT JOIN i.album a
				WHERE i.memberId = :uid AND a.allowAdd = 1')
		->setParameter('uid', $user->getId() )
		->getResult();
	}
}
?>