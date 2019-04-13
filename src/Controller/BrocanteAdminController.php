<?php

namespace App\Controller;

use App\Entity\Brocante;
use App\Form\BrocanteType;
use App\Repository\BrocanteRepository;
use App\Service\Base64Encoder;
use Doctrine\Common\Persistence\ObjectManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BrocanteAdminController
 *
 * @package AppBundle\Controller
 *
 * @Route("/admin/brocante", methods={"GET", "POST"})
 *
 * @Security("is_granted('ROLE_ADMIN')")
 */
class BrocanteAdminController extends AbstractController
{
    /**
     * @Route("s{page}", name="admin-brocantes", defaults={"page": 1}, requirements={"page": "\d+"})
     *
     * @param BrocanteRepository $repo
     * @param                    $page
     *
     * @return Response
     */
    public function index(BrocanteRepository $repo, $page)
    {
        $adapter = new DoctrineORMAdapter($repo
            ->createQueryBuilder('b')
            ->select('b')
            ->orderBy('b.date', 'DESC')
        );

        $pager = new Pagerfanta($adapter);
        $pager->setCurrentPage($page);

        return $this->render('admin/brocante/index.html.twig', [
            'pager' => $pager,
        ]);
    }

    /**
     * @Route("/creer", name="admin-brocante_creer")
     *
     * @param Request       $request
     * @param ObjectManager $manager
     * @param Base64Encoder $encoder
     *
     * @return RedirectResponse|Response
     */
    public function createBrocante(Request $request, ObjectManager $manager, Base64Encoder $encoder)
    {
        $brocante = new Brocante();

        $form = $this->createForm(BrocanteType::class, $brocante)
            ->add('submit', SubmitType::class, [
                'label' => 'Créer la brocante',
                'attr'  => [
                    'class' => 'btn btn-success pull-left',
                ],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Annuler',
                'attr'  => [
                    'class'          => 'btn btn-secondary pull-right',
                    'formnovalidate' => 'formnovalidate',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->get('cancel')->isClicked()) {
            // Si le bouton d'annulation est cliqué

            return $this->redirectToRoute('admin-brocantes');
        }

        if ($form->isSubmitted() && $form->isValid() && $form->get('submit')->isClicked()) {
            // Si le bouton de validation est cliqué
            // On récupère le fichier
            $tmpfname = $form['image']->getData();

            if ($tmpfname) {
                $base64 = $encoder->encode($tmpfname);
                $brocante->setImage($base64);
            } else {
                $brocante->setImage(null);
            }

            $manager->persist($brocante);
            $manager->flush();

            return $this->redirectToRoute('admin-brocantes_consulter', [
                'id' => $brocante->getIdBrocante(),
            ]);
        }

        return $this->render('admin/brocante/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/editer/{id}", name="admin-brocante_editer")
     *
     * @param Brocante      $brocante
     * @param Request       $request
     * @param ObjectManager $manager
     * @param Base64Encoder $encoder
     *
     * @return RedirectResponse|Response
     */
    public function editBrocante(Brocante $brocante, Request $request, ObjectManager $manager, Base64Encoder $encoder)
    {
        // Récupération de l'image de la brocante pour l'utiliser lors de la validation du formulaire
        $old_image = $brocante->getImage();

        $form = $this->createForm(BrocanteType::class, $brocante)
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr'  => [
                    'class' => 'btn btn-success pull-left',
                ],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Annuler',
                'attr'  => [
                    'class'          => 'btn btn-secondary pull-right',
                    'formnovalidate' => 'formnovalidate',
                ],
            ])
            ->add('isImage', CheckboxType::class, [
                // mapped = false pour ne pas l'inclure dans l'entité Brocante ce qui déclencherait une erreur
                'mapped'   => false,
                'required' => false,
                'label'    => "Souhaitez-vous modifier l'image de la brocante ?",
            ]);

        $form->handleRequest($request);

        if ($form->get('cancel')->isClicked()) {
            // Si le bouton d'annulation est cliqué

            return $this->redirectToRoute('admin-brocantes_consulter', [
                'id' => $brocante->getIdBrocante(),
            ]);
        }

        if ($form->isSubmitted() && $form->isValid() && $form->get('submit')->isClicked()) {
            // Si le bouton de validation est cliqué

            if ($form['isImage']->getViewData() == 1) {
                // Si on a cliqué sur la modification d'image

                // On récupère le fichier
                $tmpfname = $form['image']->getData();

                if ($tmpfname) {
                    // Si on a un fichier

                    // On encode l'image en base64
                    $base64 = $encoder->encode($tmpfname);

                    // On renseigne l'image dans la brocante
                    $brocante->setImage($base64);
                } else {
                    // Si on a pas de fichier

                    // On met à null l'image de la brocante
                    $brocante->setImage(null);
                }
            } else {
                // Si on a pas cliqué sur la modification d'image

                // On remet l'ancienne image
                $brocante->setImage($old_image);
            }

            $manager->persist($brocante);
            $manager->flush();

            return $this->redirectToRoute('admin-brocantes_consulter', [
                'id' => $brocante->getIdBrocante(),
            ]);
        }

        return $this->render('admin/brocante/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/consulter/{id}", name="admin-brocantes_consulter")
     *
     * @param Brocante $brocante
     *
     * @return Response
     */
    public function show(Brocante $brocante)
    {
        return $this->render('admin/brocante/show.html.twig', [
            'brocante' => $brocante,
        ]);
    }
}