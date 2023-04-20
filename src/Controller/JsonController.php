<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Salle;
use App\Entity\Reservation;
use DateTimeImmutable;
use Symfony\Bundle\SecurityBundle\Security;
// date type 
use Symfony\Component\Form\Extension\Core\Type\DateType;

#[Route(name: 'jsonController')]
class JsonController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function ajouterSallesEtReservations(Security $security,): void
    {
        $json = file_get_contents('../edt.json');
        $data = json_decode($json, true);

        foreach ($data['VCALENDAR'][0]['VEVENT'] as $item) {
            // Vérifier si la salle existe déjà
            if (array_key_exists('LOCATION;LANGUAGE=fr', $item)) {
                $roomName = $item['LOCATION;LANGUAGE=fr'];
                $roomNames = explode('\\,', $roomName); // On divise le nom en plusieurs parties
                $roomName = preg_replace('/\s*\([^)]*\)/', '', $roomNames[0]); // On ne garde que la première partie
                $salle = $this->entityManager->getRepository(Salle::class)->findOneBy(['nom_salle' => $roomName]);
                $disponibilite = 1;
                if (array_key_exists('LOCATION;LANGUAGE=fr', $item)) {
                    $reservation = $this->entityManager->getRepository(Reservation::class)->findOneBy(['date_debut' => new DateTimeImmutable($item['DTSTART']), 'date_fin' => new DateTimeImmutable($item['DTEND']), 'salle_reservation' => $salle]);
                    if ($salle == null) {
                        $salle = new Salle();
                        $salle->setNomSalle($roomName);
                        $salle->setDisponibiliteSalle($disponibilite);
                        $this->entityManager->persist($salle);
                        $this->entityManager->flush();
                        if ($reservation == null) {
                            $reservation = new Reservation();
                            $reservation->setSalleReservation($salle);
                            $reservation->setUserReservation($security->getUser());
                            $reservation->setSalleReservation($salle);
                            $reservation->setDateDebut(new DateTimeImmutable($item['DTSTART']));
                            $reservation->setDateFin(new DateTimeImmutable($item['DTEND']));
                            $this->entityManager->persist($reservation);
                            $this->entityManager->flush();
                        }
                    }

                    if ($reservation == null) {
                        $reservation = new Reservation();
                        $reservation->setSalleReservation($salle);
                        $reservation->setUserReservation($security->getUser());
                        $reservation->setSalleReservation($salle);
                        $reservation->setDateDebut(new DateTimeImmutable($item['DTSTART']));
                        $reservation->setDateFin(new DateTimeImmutable($item['DTEND']));
                        $this->entityManager->persist($reservation);
                        $this->entityManager->flush();
                    }
                }
            }
        }
    }
}
