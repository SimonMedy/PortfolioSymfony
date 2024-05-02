<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Experience;
use App\Form\ExperienceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExperienceController extends AbstractController
{
    #[Route('/experiences', name: 'app_experiences')]
    public function experiences(EntityManagerInterface $em): Response
    {
        $experiences = $em->getRepository(Experience::class)->findAll();
        return $this->render('experience/index.html.twig', [
            'controller_name' => 'ExperienceController',
            'experiences' => $experiences
        ]);
    }

    #[Route('/create/experience', name: 'create_experience')]
    public function createExperience(EntityManagerInterface $em, Request $request,): Response
    {
        $experience = new Experience();

        $form = $this->createForm(ExperienceType::class, $experience);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //le formulaire envoyé est valide
            $em->persist($experience);
            $em->flush();
            $this->addFlash('success', '✅Experience ajoutée avec succès !');
            return $this->redirectToRoute('app_experiences');
        }

        return $this->render('experience/experience_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/experience/{id}', 'experience', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function experience(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $experience = $em->getRepository(Experience::class)->find($id);

        if ($experience == null) {
            return $this->render('/experience/experience_notfound.html.twig');
        }

        $form = $this->createForm(ExperienceType::class, $experience);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($experience);
            $em->flush();
            $this->addFlash('success', '✅Experience modifiée avec succès !');
            return $this->redirectToRoute('experience', ['id' => $experience->getId()]);
        }

        return $this->render('/experience/experience.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'experience' => $experience,
        ]);
    }

    #[Route('experience/delete/{id}', 'deleteExperience', methods: ['POST', 'GET'], requirements: ['id' => '\d+'])]
    public function deleteExperience(Experience $experience = null, EntityManagerInterface $em)
    {
        if ($experience === null) {
            return $this->render('/experience/experience_notfound.html.twig');
        }

        $em->remove($experience);
        $em->flush();
        $this->addFlash('success', '✅Experience supprimée avec succès !');
        return $this->redirectToRoute('app_accueil');
    }
}
