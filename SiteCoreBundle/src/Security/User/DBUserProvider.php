<?php
namespace Site\CoreBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManager;
use Site\CoreBundle\Entity\UserConfigInfo;

/**
 * Сервис, предоставляющий методы для получения пользовательских данных
 * Используется при аутентификации
 * 
 * @author LNV
 *
 */
class DBUserProvider implements UserProviderInterface
{
	protected $em;
	
	/**
	 * Конструктор класса, нужно понимать, что получать мы будем экземпляры
	 * как сервисы, поэтому все нужное надо передать в конструктор в качестве аргументов.
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}
	
	/**
	 * Данный метод необходим для получения пользователя по логину (описан в интерфейсе)
	 * (non-PHPdoc)
	 * @see \Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
	 */
	public function loadUserByUsername($username) {
		if ($username !== '') {
			// Подгрузка репозитория
			$repository = $this->em->getRepository('SiteCoreBundle:UserConfigInfo');
			// Поиск пользователя (false, если не найдёт)
			$user = $repository->createQuery('SELECT u FROM UserConfigInfo u WHERE u.username = :username')
		     		->setParameter('username', $username)
		     		->getSingleResult();
			if (is_null($user))
				throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
			else
				return $user;
		} else
			throw new UsernameNotFoundException('No user name');
	}
	
	/**
	 * Получение пользователя по id (используется в аутентификации IPB 1.3)
	 * @param integer $id
	 * @throws UsernameNotFoundException
	 * @return UserInterface
	 */
	public function loadUserById($id) {
		// Поиск пользователя (false, если не найдёт)
		$user = $this->em->getRepository('SiteCoreBundle:UserConfigInfo')->find($id);
		if (is_null($user))
			throw new UsernameNotFoundException(sprintf('No user exist with id="%s".', $id));
		else
			return $user;
	}
	
	/**
	 * Метод используется при новом запросе авторизованного пользователя.
	 * Обновляет иноформацию о пользователе в сессии из БД.
	 * Нахрена это надо и как работает, я пока не знаю, но лишних запросов точно нет.
	 * 
	 * (non-PHPdoc)
	 * @see \Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
	 */
	public function refreshUser(UserInterface $user) {
		if (!$user instanceof UserConfigInfo) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
		}	
		return $this->loadUserById($user->getId());
	}
	
	/**
	 * Метод проверки класса пользователя.
	 * (non-PHPdoc)
	 * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
	 */
	public function supportsClass($class) {
		return $class === 'Site\CoreBundle\Security\User\UserConfigInfo';
	}
}
?>