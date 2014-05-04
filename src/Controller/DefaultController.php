<?php

namespace Site\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
	/**
	 * @Template()
	 */
    public function indexAction() {
        return array();
    }
    
    
    public function loginAction()
    {
    	$request = $this->getRequest();
    	$session = $request->getSession();
    	
$response = new Response();
$cookie = new Cookie('member_id', 19335, time() + 3600 * 24 * 7);
$response->headers->setCookie($cookie);
$cookie = new Cookie('pass_hash', md5(123), time() + 3600 * 24 * 7);
$response->headers->setCookie($cookie);
$response->send();
    
    	// �������� ������ ������, ���� ������� �������
    	if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
    		$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
    	} else {
    		$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
    	}
    
    	return $this->render('SiteCoreBundle:Default:login.html.twig', array(
    			// ���, �������� ������������� � ��������� ���
    			'last_username' => $session->get(SecurityContext::LAST_USERNAME),
    			'error'         => $error,
    	));
    }
    
    /**
     * @Template()
     */
    public function headerAction() {
    	return array();
    }
    
    /**
     * @Template()
     */
    public function menuAction() {
    	$em = $this->getDoctrine()->getManager();
    	$navigate = $em
    	->createQuery(
    			'SELECT m, mi
				FROM SiteCoreBundle:MenuCategory m
				LEFT JOIN m.menuItems mi
    			ORDER BY m.position, mi.position ASC')->getResult();
    	return array('navigate' => $navigate);
    }
}
