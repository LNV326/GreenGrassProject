<?php
namespace Site\CoreBundle\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Site\CoreBundle\Security\Authentication\Token\IPB13Token;

/**
 * Сервис, осуществляющий сбор данных от пользователя, необходимых для аутентификации.
 * В процессе работы, создаёт токен, который отправляет на проверку менеджеру аутентификации.
 * @author LNV
 *
 */
class AuthListener implements ListenerInterface
{
	protected $securityContext;
	protected $authenticationManager;

	/**
	 * Как любой конструктор сервиса, принимает все инструменты в качестве входных параметров
	 * @param SecurityContextInterface $securityContext
	 * @param AuthenticationManagerInterface $authenticationManager
	 */
	public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager) {
		$this->securityContext = $securityContext; // Механизм разграничения доступа, наверное...
		$this->authenticationManager = $authenticationManager; // Менеджер аутентификации
	}

	/**
	 * Метод, осуществляющий сбор данных, создающий токен и отправляющий его на проверку
	 * (non-PHPdoc)
	 * @see \Symfony\Component\Security\Http\Firewall\ListenerInterface::handle()
	 */
	public function handle(GetResponseEvent $event) {
		// Получаем печеньки
		$request = $event->getRequest();
		$cookies = $request->cookies;
		// Проверяем печеньки, заполняем поля
		$userId = abs(intval($cookies->get('member_id')));
		$passHash = strval($cookies->get('pass_hash'));
		$sessionId = strval($cookies->get('session_id'));
		// Создаём токен для авторизации
		$token = new IPB13Token();
		$token->userId = $userId;
		$token->passHash = $passHash;
		$token->sessionId = $sessionId;
		// … и передаем его на проверку
		try {
			$authToken = $this->authenticationManager->authenticate($token);
			// результатом проверки будет авторизованных токен, либо null.
			// отдаём токен (содержит информацию о пользователе) дальше...
			$this->securityContext->setToken($authToken);
			return;
		} catch (AuthenticationException $failed) {
			// В случае ошибки сброс токена, 
			$this->securityContext->setToken(null);
			return;
		}
		// Пока не хочу возвращать 403. Посмотрим, что будет
// 		$response = new Response();
// 		$response->setStatusCode(403);
// 		$event->setResponse($response);
// 		return $response;
	}
}
?>