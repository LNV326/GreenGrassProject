<?php
namespace Site\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DictionaryItemRep extends EntityRepository {
	/**
	 * Возвращает элемент списка выбора
	 * @param string $objId
	 * @param string $refId
	 * @return NULL|Ambigous <\Doctrine\ORM\mixed, mixed, \Doctrine\ORM\Internal\Hydration\mixed, \Doctrine\DBAL\Driver\Statement, \Doctrine\Common\Cache\mixed>
	 */
	public function getItem($objId=null, $refId=null) {
		if (is_null($objId) and is_null($refId))
			return null;
		if (!is_null($objId)) {
			return $this->getEntityManager()
			->createQuery('SELECT di
				FROM SiteCoreBundle:DictionaryItem di
				WHERE di.objId = :id')
						->setParameter('id', $objId)
						->getSingleResult();
		}
		if (!is_null($refId)) {
			return $this->getEntityManager()
			->createQuery('SELECT di
				FROM SiteCoreBundle:DictionaryItem di
				WHERE di.refId = :id')
						->setParameter('id', $refId)
						->getSingleResult();
		}
	}
}
?>