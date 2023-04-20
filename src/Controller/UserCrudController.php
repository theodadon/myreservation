<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Controller\JsonController;
use App\Entity\Reservation;
use \Symfony\Bundle\SecurityBundle\Security;
use App\Repository\ReservationRepository;
use PharIo\Manifest\Url;

#[Route('/user')]
class UserCrudController extends AbstractController
{


    // fonction pour afficher les données de l'utilisateur connecté
    #[Route('/', name: 'app_user_crud_index', methods: ['GET'])]
    public function showUser(JsonController $jsonController, Security $security): Response
    {
        $jsonController->ajouterSallesEtReservations($security);
        $user = $this->getUser();
        return $this->render('user_crud/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/show', name: 'app_user_crud_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user_crud/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $pass): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $pass->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_crud/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_crud_delete_reservation', methods: ['GET', 'POST'])]
    public function delete_reservation(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $reservation = $reservationRepository->find($reservation->getId());
        $reservationRepository->remove($reservation, true);
        return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
