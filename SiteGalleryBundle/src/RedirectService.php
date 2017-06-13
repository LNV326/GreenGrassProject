<?php

namespace Site\GalleryBundle;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\NoResultException;

/*
 * Список примеров URL страниц старой галереи
 * http://images.nfsko.ru/index.php?page=gallery&cat=17
 * http://images.nfsko.ru/index.php?page=gallery&view=160
 * http://images.nfsko.ru/index.php?page=gallery&user=22753
 * http://images.nfsko.ru/index.php?page=gallery&view=my_files
 * http://images.nfsko.ru/image.php?gallery=view&image=1542
 */
class RedirectService {
	
	const REDIRECT_CODE = 301;
	
	protected $container = null;
	protected $request = null;
	protected $logger = null;
	
	public function __construct(Container $container, Logger $logger) {
		$this->container = $container;
		$this->request = $container->get('request');
		$this->logger = $logger;
	}
	
	public function getRedirect() {
		$query = $this->request->query;
		$logger = $this->logger;
		$gallery = $this->container->get('gallery_service');
		// Редирект для категории
		if ( $query->has('cat') ) {
			$id = $query->get('cat');
			try {
				$category = $gallery->getCategory($id);
				$logger->info( sprintf('Редирект %d с %s в категорию "%s" - для %s', self::REDIRECT_CODE, $this->request->getQueryString(), $category->getRefId(), $this->request->headers->get('User-Agent') ) );
				return array(
					'route' => 'site_gallery_category',
					'arg' => array(
						'cRefId' => $category->getRefId()
					),
					'redirect_code' => self::REDIRECT_CODE
				);
			} catch (NoResultException $e) {
				$logger->crit( sprintf('Ошибка редиректа категории для %s', $this->request->getQueryString() ) );
				throw new NotFoundHttpException(sprintf('Категория %s не существует',$id));
			}
		}
		// Редирект для альбома
		if ( $query->has('view') ) {
			$id = $query->get('view');
			$repository = $this->container->get('doctrine')->getManager()->getRepository('SiteGalleryBundle:ImageAlbum');
			try {
				$album = $repository->getAlbumById($id);
				$logger->info( sprintf('Редирект %d с %s в альбом "%s" в категории "%s" - для %s', self::REDIRECT_CODE, $this->request->getQueryString(), $album->getDictionary()->getRefId(), $album->getCategory()->getRefId(), $this->request->headers->get('User-Agent') ) );
				return array(
						'route' => 'site_gallery_album',
						'arg' => array(
								'cRefId' => $album->getCategory()->getRefId(),
								'aRefId' => $album->getDictionary()->getRefId()
						),
						'redirect_code' => self::REDIRECT_CODE
				);
			} catch (NoResultException $e) {
				$logger->crit( sprintf('Ошибка редиректа альбома для %s', $this->request->getQueryString() ) );
				throw new NotFoundHttpException(sprintf('Альбома %s не существует',$id));
			}
		}
		// Редирект для изображения
		if ( $query->has('image') ) {
			$id = $query->get('image');
			try {
				$image = $gallery->getImage($id, false, false);
				$logger->info( sprintf('Редирект %d с %s на изображение "%d" - для %s', 302, $this->request->getQueryString(), $image->getId(), $this->request->headers->get('User-Agent') ) );
				return array(
						'route' => 'site_gallery_image',
						'arg' => array(
								'iId' => $image->getId()
						),
						'redirect_code' => 302
				);				
			} catch (NoResultException $e) {
				$logger->crit( sprintf('Ошибка редиректа изображения для %s', $this->request->getQueryString() ) );
				throw new NotFoundHttpException(sprintf('Изображения %s не существует',$id));
			}
		}
		// Редирект для пользователя
		if ( $query->has('user') ) {
			$id = $query->get('user');
			$repository = $this->container->get('doctrine')->getManager()->getRepository('SiteCoreBundle:UserConfigInfo');
			try {
				// TODO Получение пользователя необходимо вынести в отдельный метод
				if ( $user = $repository->find( $id ) ) {
					$logger->info( sprintf('Редирект %d с %s в изображения пользователя "%d" - для %s', self::REDIRECT_CODE, $this->request->getQueryString(), $user->getId(), $this->request->headers->get('User-Agent') ) );
					return array(
							'route' => 'site_gallery_userImages',
							'arg' => array(
									'uId' => $user->getId()
							),
							'redirect_code' => self::REDIRECT_CODE
					);
				} else throw new NoResultException('');
			} catch (NoResultException $e) {
				$logger->crit( sprintf('Ошибка редиректа пользовательских изображений для %s', $this->request->getQueryString() ) );
				throw new NotFoundHttpException(sprintf('Пользователя %s не существует',$id));
			}
		}
		return null;
	}
}

?>
