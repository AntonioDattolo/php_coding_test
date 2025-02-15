<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Serializer\SerializerInterface;

final class CarController extends AbstractController
{
    //METODO CREATE
    #[Route('/car/add', name: 'app_car')]
    public function createCar(EntityManagerInterface $entityManager ,ValidatorInterface $validator){
            $newCar = new Car();
            $newCar->setBrand('BMW');
            $newCar->setModel('M2');
            $newCar->setPrice(999.02);
            $newCar->setProductionYear(2021);

            // Validazione dell'oggetto prima di essere salvato
            $errors = $validator->validate($newCar);
            if (count($errors) > 0) {
                // se ci sono errori, restituisce una risposta 400
                return new Response((string) $errors, 400);
            }

            try{
            $entityManager->persist($newCar);

            $entityManager->flush();
            }
            catch(\Exception $e){
                // Gestisce eventuali errori durante il salvataggio
                return new Response('Error saving car: ' . $e->getMessage());
            }

            // Successo: ritorna l'ID del nuovo oggetto creato
            return new Response('Saved new Car : '.$newCar->getBrand().' '.$newCar->getModel());



    }


    // METODO SHOW
    #[Route('/car/{id}', name: 'car_show')]
    public function show(EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer){
        $car = $entityManager->getRepository(Car::class)->find($id);

        //controllo se l'oggetto è presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        // ritorna l'oggetto in formato json
        $jsonCar = $serializer->serialize($car, 'json');

        return new Response($jsonCar, 200, ['Content-Type' => 'application/json']);
        

    }


    // METODO CON DI EDIT
    #[Route('/car/edit/{id}', name: 'car_edit')]
    public function edit(EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer){
        //trova l'oggetto secondo l'id
        $car = $entityManager->getRepository(Car::class)->find($id);

        //controllo se l'oggetto è presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        //edito l'oggetto

        $car->setBrand('Audi');
        $car->setModel('RS3');
        $car->setProductionYear(2023);
        $car->setPrice(857.22);
        
        try{
            //  estratto dalla documentazione , è possibile richiamare il metodo persist(), ma non ce n'è il bisogno
            //  perchè Doctrine è già FOCUSSATO sull'oggeto interessato per l'update
            $entityManager->flush();
        }catch(\Exception $e){
            return new Response('Error. Cant Update Obj: ' . $e->getMessage());    
        }

        return $this->redirectToRoute('car_show',['id' => $car->getId()]);
    }
    

    //METODO DELETE
    #[Route('/car/delete/{id}', name: 'car_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id){
        $car = $entityManager->getRepository(Car::class)->find($id);
        //HARD DELETE
        // $entityManager->remove($car);
        // $entityManager->flush();

        //SOFT DELETE
        $car->setDeletedAt(new \DateTime());
        $entityManager->flush();
        // Successo: ritorna l'ID del nuovo oggetto creato
        return new Response('Car deleted : ');

    }
    public function index(): Response
    {
        return $this->render('car/index.html.twig', [
            'controller_name' => 'CarController',
        ]);
    }
}
