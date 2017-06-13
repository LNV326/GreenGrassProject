<?php
namespace Site\CoreBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Класс токена авторизации, включающий в себя поля, используемые при авторизации на сайте/форуме в IPB 1.3
 * К этим полям относится id пользователя, хэш пароля, а также id сессии (пока не используется)
 * 
 * Токен - это паспорт пользователя, имеющий серию, номер, и кем-то выданный 
 * @author LNV
 *
 */
class IPB13Token extends AbstractToken
{
	public $userId = '';
	public $passHash = '';
	public $sessionId = '';

    // Стандартный конструктор, обращающийся к родителю 
    function __construct(array $roles = array()) {
        parent::__construct($roles);          
        // по примеру встроенной аутентификации, добавляем в конструктор 
        // прямое указание на аутентификацию токена с не пустым списком ролей.
        parent::setAuthenticated(count($roles) > 0);
    }

    // Метод, необходимый для реализации TokenInterface
	public function getCredentials() {
		return '';
	}
	
	// Поскольку токены проверяются при обработке каждом новом запросе клиента,
	// нам необходимо сохранять нужные нам данные. В связи с этим “обертываем”
	// унаследованные методы сериализации и десериализации.
	public function serialize() {
		$pser = parent::serialize();
		return serialize(array($this->userId, $this->passHash, $this->sessionId, $pser));
	}
	
	public function unserialize($serialized) {
		list($this->userId, $this->passHash, $this->sessionId, $pser) = unserialize($serialized);
		parent::unserialize($pser);
	}
}
