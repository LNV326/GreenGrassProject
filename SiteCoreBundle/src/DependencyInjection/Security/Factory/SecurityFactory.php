<?php
namespace Site\CoreBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

/**
 * Какой-то мутный класс, который сводит воедино все сервисы аутентификации (Listener`ы, Provider`ы) и связывает друг с другом
 * @author LNV
 *
 */
class SecurityFactory implements SecurityFactoryInterface {
	/**
	 * Основной метод, в котором мы связываем в единый firewall наши listener и provider.
	 * В качестве аргументов метод принимает контейнер, id firewall’а из
	 * конфигурации, а так же прописанные в конфигурации параметры.
	 * (non-PHPdoc)
	 * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::create()
	 */
	public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
	{
		// К сожалению в документации не разъясняется, что именно и как
		// делает этот метод.
		// В общих чертах здесь производит “обертывание” наших сервисов
		// (о них ниже) с частичной заменой аргументов.
		
		// Объявлется id нового сервиса для AuthenticationProvider’а
		$providerId = 'security.authentication.provider.ipb13_sec.'.$id;
		// Затем создается сервис - декоратор для нашего ipb13_sec.security.authentication.provider
		// и первый(0й) аргумент заменяется на ссылку на один из аргументов
		// данного метода
		$container->setDefinition($providerId, new DefinitionDecorator('ipb13_sec.security.authentication.provider'))
		->replaceArgument(0, new Reference($userProvider));
		// для listener’а совершаются те же действия, что и для provider’а
		$listenerId = 'security.authentication.listener.ipb13_sec.'.$id;
		$container->setDefinition($listenerId, new DefinitionDecorator('ipb13_sec.security.authentication.listener'));
		// метод возвращает id новых сервисов.
		return array($providerId, $listenerId, $defaultEntryPoint);
	}

	/**
	 * Метод сообщает, на каком этапе обработки запроса следует использовать наши классы. Снова делаем по аналогии с login_form.
	 * (non-PHPdoc)
	 * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::getPosition()
	 */
	public function getPosition() {
		return 'pre_auth';
	}

	/**
	 * Метод сообщает имя созданной нами подсистемы, это имя в конфигурации даст фреймворку указание использовать наш код.
	 * (non-PHPdoc)
	 * @see \Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface::getKey()
	 */
	public function getKey() {
		return 'ipb13_sec'; // Это имя метода аутентификации
	}

	// метод создание конфигурации, практически полностью копирует аналогичный в
	// AbstractFactory
	public function addConfiguration(NodeDefinition $node) {
		// Нет тут нихрена, читай документацию
	}
}

// // В коде Symfony есть класс AbstractFactory который я почти полностью копирую.
// // Это связано с тем, что струкутра наследника показалась мне перегруженой, как и
// // в случае с AuthenticationProvider’ом
// class SecurityFactory implements SecurityFactoryInterface {

// 	// прописываем набор базовых опций будущего firewall’а и их значения
// 	// по умолчанию. Набор опций совпадает с form_login.
// 	protected $options = array(
// 			'check_path'                     => '/login_check',
// 			'login_path'                     => '/login',
// 			'use_forward'                    => false,
// 			'always_use_default_target_path' => false,
// 			'default_target_path'            => '/',
// 			'target_path_parameter'          => '_target_path',
// 			'use_referer'                    => false,
// 			'failure_path'                   => null,
// 			'failure_forward'                => false,
// 	);


// 	// метод создание конфигурации, практически полностью копирует аналогичный в
// 	// AbstractFactory
// 	public function addConfiguration(NodeDefinition $node) {
// 		$builder = $node->children();

// 		$builder
// 		->scalarNode('provider')->end()
// 		;

// 		foreach ($this->options as $name => $default) {
// 			if (is_bool($default)) {
// 				$builder->booleanNode($name)->defaultValue($default);
// 			} else {
// 				$builder->scalarNode($name)->defaultValue($default);
// 			}
// 		}
// 	}
// }
?>