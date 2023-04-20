<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Form\Reservation2Type;
use App\Repository\ReservationRepository;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\JsonController;
use Symfony\Bundle\SecurityBundle\Security;
use DateTimeImmutable;
use Symfony\Component\Form\FormError;
use Doctrine\Common\Collections\ArrayCollection;




#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/new', name: 'app_reservation_new_salle')]
    public function new_salle(Security $security, JsonController $jsonController, Request $request, SalleRepository $salleRepository): Response
    {
        #$oui = $jsonController->ajouterSallesEtReservations($security);

        // get all the salles
        $salles = $salleRepository->findAll();
        //get all the name of the salles
        $sallesName = new \ArrayObject();
        foreach ($salles as $salle) {
            $sallesName[$salle->getNomSalle()] = $salle->getId();
        }
        $form = $this->createForm(ReservationType::class, null, [
            'salles' => $sallesName,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDuForm = $form->get('date')->getData();
            $dateDuForm = $dateDuForm->format('Y-m-d');
            $salleDuForm = $form->get('salle_reservation')->getData();

            return $this->redirectToRoute('app_reservation_new_horaires', [
                'salle' => $salleDuForm,
                'date' => $dateDuForm,
            ]);
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(name: 'app_reservation_new_horaires')]
    public function new_horaires(Request $request, ReservationRepository $reservationRepository, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        $salle = $request->query->get('salle');
        $salle = $salleRepository->findOneBy(['id' => $salle]);
        $date = $request->query->get('date');
        $date = new \DateTime($date);
        $date = $date->format('Y-m-d');
        $bonnedate = $date;
        $date = $date . ' 00:00:00';
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date);
        //find all the reservations for the salle for the date
        $reservationprise = $reservationRepository->findBy(['salle_reservation' => $salle]);
        $reservationprise = $reservationRepository->findByDate($date);
        $debutpris = new ArrayCollection();
        $finpris = new ArrayCollection();
        if ($reservationprise) {
            foreach ($reservationprise as $reservation) {
                $debutpris->add($reservation->getDateDebut()->format('H:i'));
                $finpris->add($reservation->getDateFin()->format('H:i'));
            }
        }
        $horaireDebut = ["8:00", "9:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00"];
        $horaireFin = ["9:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00"];
        $form = $this->createForm(Reservation2Type::class, null, [
            'horairedebut' => array_values($horaireDebut),
            'horairefin' => array_values($horaireFin),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $debutReservation = $form->get('horairedebut')->getData();
            $finReservation = $form->get('horairefin')->getData();
            $debutReservation = $horaireDebut[$debutReservation];
            $finReservation = $horaireFin[$finReservation];
            if ($reservationprise) {
                foreach ($reservationprise as $reservation) {
                    $debut = $reservation->getDateDebut()->format('H:i');
                    $fin = $reservation->getDateFin()->format('H:i');
                    $controlreservation = match (true) {
                        $debutReservation == $debut || $finReservation == $fin => true,
                        $debutReservation > $finReservation || $finReservation < $debutReservation => true,
                        $debutReservation > $debut && $debutReservation < $fin => true,
                        $finReservation > $debut && $finReservation < $fin => true,
                        $debutReservation <= $debut && $finReservation >= $fin => true,
                        default => false,
                    };
                    if ($controlreservation) {
                        $form->addError(new FormError('La réservation n\'est pas possible pour cet horaire'));
                        return $this->render('reservation/new2.html.twig', [
                            'form' => $form->createView(),
                            'debutpris' => $debutpris,
                            'finpris' => $finpris,
                        ]);
                    }
                }
                $debut = new \DateTimeImmutable($bonnedate . $debutReservation);
                $fin = new \DateTimeImmutable($bonnedate . $finReservation);
                $reservation = new Reservation();
                $reservation->setDateFin($fin);
                $reservation->setUserReservation($this->getUser());
                $reservation->setDateDebut($debut);
                $reservation->setSalleReservation($salle);
                $entityManager->persist($reservation);
                $entityManager->flush();
                return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
            }
            $controlreservation = match (true) {
                $debutReservation == $finReservation => true,
                $debutReservation > $finReservation => true,
                default => false,
            };
            if ($controlreservation) {
                $form->addError(new FormError('La réservation n\'est pas possible'));
            }
            $debut = new \DateTimeImmutable($bonnedate . $debutReservation);
            $fin = new \DateTimeImmutable($bonnedate . $finReservation);
            $reservation = new Reservation();
            $reservation->setDateFin($fin);
            $reservation->setUserReservation($this->getUser());
            $reservation->setDateDebut($debut);
            $reservation->setSalleReservation($salle);
            $entityManager->persist($reservation);
            $entityManager->flush();
            return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('reservation/new2.html.twig', [
            'form' => $form->createView(),
            'debutpris' => $debutpris,
            'finpris' => $finpris,
        ]);
    }

    #[Route('/show', name: 'app_reservation_show', methods: ['GET', 'POST'])]
    public function show(ReservationRepository $reservationRepository): Response
    {

        // Voir les reservations de l'utilisateur dans l'ordre plus récent en premier
        $reservations = $reservationRepository->findBy(['user_reservation' => $this->getUser()], ['date_debut' => 'DESC']);
        return $this->render('reservation/show.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/delete', name: 'app_reservation_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        // On récupère l'id passé en paramètre
        $id = $request->query->get('id');

        // On récupère la reservation
        $reservation = $reservationRepository->findOneBy(['id' => $id]);

        // On supprime la reservation
        $entityManager->remove($reservation, true);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_crud_index');
    }
}
