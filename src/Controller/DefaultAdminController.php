<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultAdminController
 *
 * @package AppBundle\Controller
 *
 * @Route("/admin", methods={"GET"})
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class DefaultAdminController extends AbstractController
{
    /**
     * @Route("", name="accueil-admin", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('admin/index.html.twig');
    }
}
