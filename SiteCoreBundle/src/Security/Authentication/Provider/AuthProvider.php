<?php
namespace Site\CoreBundle\Security\Authentication\Provider;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Site\CoreBundle\Security\Authentication\Token\IPB13Token;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Сервис, осуществляющий непосредственную проверку токена
 * @author LNV
 *
 */
class AuthProvider implements AuthenticationProviderInterface
{
	private $userProvider;
	private $cacheDir;
	private $logger; 

	/**
	 * Конструктор сервиса, принимающий все необходимые для работы инструменты
	 * @param UserProviderInterface $userProvider
	 * @param LoggerInterface $logger
	 */
	public function __construct(UserProviderInterface $userProvider, LoggerInterface $logger) {
		$this->userProvider = $userProvider; // Поставщик пользовательских данных
		$this->logger = $logger;	// Подсистема ведения логов
	}

	/**
	 * Основной метод класса, проводит проверку токена
	 * @param TokenInterface $token
	 * @throws AuthenticationException
	 * @return \Site\CoreBundle\Security\Authentication\Token\IPB13Token
	 */
	public function authenticate(TokenInterface $token) {
		$user = null;	
		// Ищем пользователя по средствам UserProvider’a, ищем пользователя с заданным id
		if ($token instanceof IPB13Token) {
			try {
				$user = $this->userProvider->loadUserById($token->userId);
			} catch (UsernameNotFoundException $ex) {
				$this->logger->debug("Can not find user id=".$token->userId);
			}
		} else
			$this->logger->debug('Token not instance of IPB13Token');
		// Проводим проверку пользователя
		try {
			// начинаем с проверки hash’а
			if ($this->checkHash($token, $user)) {
				$this->logger->info("hash is valid");		
				// Следуя примеру из оф. документации, создаем новый Token
				// и наполняем его необходимой информацией
				$authenticatedToken = new IPB13Token($user->getRoles());
				$authenticatedToken->userId = $token->userId;
				$authenticatedToken->passHash = $token->passHash;
				$authenticatedToken->sessionId = $token->sessionId;
				$authenticatedToken->setUser($user);		
				// ...и возвращаем в качестве результата работы метода
				return $authenticatedToken;
			} else {
				$this->logger->debug("hash is invalid.");
			}
		} catch (\Exception $ex) {
			$this->logger->err("auth internal exception: $ex");
		}
		// Если по каким-то причинам аутентификация не состоялась - запускаем
		// специальное исключение.
		throw new AuthenticationException('The DB authentication failed.');
	}

	/**
	 * Метод проверки hesh’ей, осуществляющий специфические действия для каждой подключенной службы
	 * В данном случае производится проверка хэша пароля в токене и в БД
	 * @param IPB13Token $token
	 * @param UserInterface $user
	 * @return boolean
	 */
    protected function checkHash(IPB13Token $token, UserInterface $user) {
    	if ($user->getId() == 0)
    		return true;
    	else return ($token->passHash === $user->getPassword());
    }

    /**
     * Реализуем интерфейс, метод проверки совместимости token’а и provider’а
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface::supports()
     */
    public function supports(TokenInterface $token) {
        return $token instanceof IPB13Token;
    }
}
?>