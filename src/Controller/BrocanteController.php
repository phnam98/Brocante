<?php

namespace App\Controller;

use App\Entity\Brocante;
use App\Entity\Participer;
use App\Entity\Villesfr;
use App\Form\ParticipeType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;


class BrocanteController extends AbstractController
{
    /**
     * @Route("/brocante/consulter/{id}", name="brocante_show")
     *
     * @param Brocante      $brocante
     * @param Request       $request
     * @param ObjectManager $manager
     *
     * @return Response
     */
    public function show(Brocante $brocante, Request $request, ObjectManager $manager)
    {
        $participe = new Participer();
        $participe->setIdBrocante($brocante);

        if ($this->getUser()) {
            $userID = $this->get('security.token_storage')->getToken()->getUser();
            $participe->setIdUtilisateur($userID);
        }

        $form = $this->createForm(ParticipeType::class, $participe)
            ->add('submit', SubmitType::class, [
                'label' => "S'inscrire à la brocante",
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->get('submit')->isClicked()) {
            $manager->persist($participe);
            $manager->flush();
        }

        return $this->render('brocante/show.html.twig', [
            'brocante' => $brocante,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/brocante/consulter/dept/{villes_dep}", name="br_show_region")
     *
     * @param Villesfr $villes_dep
     *
     * @return Response
     */
    public function show_departement_brocantes($villes_dep)
    {
        $repo = $this->getDoctrine()->getRepository(Villesfr::class);
        $villes = $repo->findBy(['ville_departement' => $villes_dep]);

        $brocante = $this->getDoctrine()->getRepository(Brocante::class);

        $bb = null;
        foreach($villes as $v){
            if($brocante->trouveParDepartement($v->getId())){
                $bb = $brocante->trouveParDepartement($v->getId());
            }
        }


        return $this->render('brocante/br_show.html.twig', [
            'villes' => $villes,
            'brocantes' => $bb,
        ]);
    }

    /**
     * @Route("/brocante/consulter/ville/{ville}", name="br_show_ville")
     *
     * @param Villesfr $ville
     *
     * @return Response
     */
    public function show_ville_brocantes($ville)
    {
        $repo = $this->getDoctrine()->getRepository(Villesfr::class);
        $villes = $repo->findBy(['ville_nom_reel' => $ville]);

        $brocante = $this->getDoctrine()->getRepository(Brocante::class);

        $bb = null;
        foreach($villes as $v){
            if($brocante->trouveParVille($v->getId())){
                $bb = $brocante->trouveParVille($v->getId());
            }
        }

        return $this->render('brocante/br_show_ville.html.twig', [
            'villes' => $villes,
            'brocantes' => $bb,
        ]);
    }

    /**
     * @Route("/brocante/consulter/date/{date}", name="br_show_date")
     *
     * @param Date $date
     *
     * @return Response
     */
    public function show_date_brocantes($date)
    {
        $brocante = $this->getDoctrine()->getRepository(Brocante::class);

        $date =  date_format($date, "%d-%m-%Y");
        $bb = null;

            if($brocante->trouveParDate($date)){
                $bb = $brocante->trouveParDate($date);
            }

            dump($bb);


        return $this->render('brocante/br_show_date.html.twig', [
            'brocantes' => $bb,
        ]);
    }
}
