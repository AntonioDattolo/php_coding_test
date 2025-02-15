<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarController extends AbstractController
{
    #[Route('/car', name: 'app_car')]
    public function createCar(EntityManagerInterface $entityManager){
            $newCar = new Car();
            $newCar->setBrand('BMW');
            $newCar->setModel('M2');
            $newCar->setPrice(999,02);
            $newCar->setProductionYear(2021);

            try{
            $entityManager->persist($newCar);

            $entityManager->flush();
            }
            catch(\Exception $e){
                return new Response('Error saving car: ' . $e->getMessage());
            }

            return new Response('Saved new Car with id '.$newCar->getId());



    }
    public function index(): Response
    {
        return $this->render('car/index.html.twig', [
            'controller_name' => 'CarController',
        ]);
    }
}
