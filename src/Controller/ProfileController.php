<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserParamsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
#[IsGranted(User::ROLES['user'])]
class ProfileController extends BaseController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserParamsType::class, $user->getParams());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setParams($form->getData());

            $entityManager->flush();

            $this->addFlash('success', 'User data have been saved.');

            return $this->redirectToRoute('app_default');
        }

        return $this->render('default/profile.html.twig', [
            'form' => $form,
        ]);
    }
}
