<?php
namespace Site\GalleryBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;

use Site\GalleryBundle\Entity\ImageCategory;

class DefaultController extends Controller {
	// Возможные результаты работы функции
	const ST_SUCCESS = 'Success';
	const ST_FAIL = 'Fail';
	
	// Коэффициенты расчёта доступного пользователю дискового пространства
	const K_POSTS_MIN = 10; // Минимальное число тематических сообщений
	const K_POSTS = 250; // Инкремент тематических сообщений
	const K_KB = 750; // + Кб за каждые K_POSTS тематических сообщений
		
	protected $freeSpace = 0;
	protected $occupSpace = 0;
	protected $totalSpace = 0;
	
	// Параметры, содержащие конечный ответ
	protected $action = null;
	protected $status = null;
	protected $body = array();
	protected $error = array();
	
// 	public function initVars() {
// 		$this->imageHostName = $this->container->getParameter('img_host');
		
// // 		if ( !$this->getRequest()->isXmlHttpRequest() ) {
// // 			$this->getCategoryList(false);
// // 		}
// 	}
	
	protected function createResponse() {
		return array(
				'action' => $this->action,
				'status' => count($this->error)==0 ? self::ST_SUCCESS : self::ST_FAIL,
				'body' => $this->body,
				'error' => $this->error
		);
	}
	
// 	/**
// 	 * Возвращает доступное пользователю дисковое пространство
// 	 * Исключаются из расчёта изображения, загруженные админами в закрытые альбомы
// 	 * @return number
// 	 */
// 	protected function getUserSpace() {
// 		$posts = $this->getUser()->getPostsCount() - $this->getUser()->getPostsBadCount();
// 		if ( $posts >= self::K_POSTS_MIN ) {
// 			$inc = ceil( $posts/self::K_POSTS );
// 			$this->totalSpace = $inc * self::K_KB * 1024; // Расчёт максимального доступного пространства к байтах
// 			// Расчёт занятого пространства
// 			$repo = $this->getDoctrine()->getManager()->getRepository('SiteGalleryBundle:Image');
//  			$images = $repo->getUserImages( $this->getUser()->getId() );
// 			foreach ($images as $image) {
// 				try {
// 					$this->occupSpace += filesize( $image->getAbsolutePath() );
// 				} catch (\Exception $e) {}
// 			}
// 			$this->freeSpace = $this->totalSpace - $this->occupSpace;
// 		}
// 		return $this->freeSpace;
// 	}
}
?>