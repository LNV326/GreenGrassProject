<?php

namespace Site\GalleryBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Site\CoreBundle\DependencyInjection\Security\Factory\SecurityFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SiteGalleryBundle extends Bundle
{
	public function build(ContainerBuilder $container) {
		parent::build($container);
		$extension = $container->getExtension('security');
		$extension->addSecurityListenerFactory(new SecurityFactory());
	}
}
