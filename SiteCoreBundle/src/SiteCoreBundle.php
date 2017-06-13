<?php

namespace Site\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Site\CoreBundle\DependencyInjection\Security\Factory\SecurityFactory;

class SiteCoreBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
// 		$extension = $container->getExtension('security');
// 		$extension->addSecurityListenerFactory(new SecurityFactory());
	}
}
