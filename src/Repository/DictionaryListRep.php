<?php
namespace Site\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DictionaryListRep extends EntityRepository
{
	/**
	 * Возвращает запись в справочнике и его элементы (choiselist)
	 * @param string $listId Идентификатор справочника
	 * @param string $refId Ссылочное название справочника
	 * @return NULL
	 */
	public function getDicListWithItems($objId, $refId=null, $notInObjId = -1) {
		if (is_null($objId) and is_null($refId))
			return null;
		if (!is_null($objId)) {
			return $this->getEntityManager()
			->createQuery('SELECT dl, di
				FROM SiteCoreBundle:DictionaryList dl
				LEFT JOIN dl.items di
				WHERE dl.objId = :id and di.objId not in (:notInObjId)')
							->setParameter('id', $objId)
							->setParameter('notInObjId', $notInObjId)
							->getSingleResult();
		}
		if (!is_null($refId)) {
			return $this->getEntityManager()
			->createQuery('SELECT dl, di
				FROM SiteCoreBundle:DictionaryList dl
				LEFT JOIN dl.items di
				WHERE dl.referenceName = :id and di.objId not in (:notInObjId)')
							->setParameter('id', $refId)
							->setParameter('notInObjId', $notInObjId)
							->getSingleResult();
		}
	}
}
?>