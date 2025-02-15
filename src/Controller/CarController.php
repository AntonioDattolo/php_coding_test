<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Serializer\SerializerInterface;

// gestisce il listener per intercettare gli errori 
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//
use Symfony\Component\HttpFoundation\JsonResponse;

final class CarController extends AbstractController
{  
    //METODO CREATE
    #[Route('/car/add', name: 'app_car')]
    public function createCar(EntityManagerInterface $entityManager ,ValidatorInterface $validator){
            $newCar = new Car();
            $newCar->setBrand('BMW');
            $newCar->setModel('M2');
            $newCar->setPrice(999.02);
            $newCar->setProductionYear(2020);
            $newCar->setState(true); 
            $newCar->setIsNew(rand(0, 1) == 1); 
            $newCar->setCreatedAt(new \DateTime()); 
            $newCar->setUpdatedAt(new \DateTime()); 

            // Validazione dell'oggetto prima di essere salvato
            $errors = $validator->validate($newCar);

            if (count($errors) > 0) {
                // se ci sono errori, restituisce una risposta 400
                return new Response((string) $errors, 400);
            }

            try
            {
                $entityManager->persist($newCar);
                $entityManager->flush();
            }
            catch(\Exception $e){
                // Gestisce eventuali errori durante il salvataggio
                return new Response('Error cant saving car: ' . $e->getMessage());
            }

            // Successo: ritorna il brand e il modello del nuovo oggetto creato
            return new JsonResponse(['success' => 'Saved new Car', 'car' => ['brand' => $newCar->getBrand(), 'model' => $newCar->getModel()]], 201);

    }


    // METODO SHOW
    #[Route('/car/{id}', name: 'car_show')]
    public function show(EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer){
        $car = $entityManager->getRepository(Car::class)->find($id);

        //controllo se l'oggetto è presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
            // throw new NotFoundHttpException('Car not found');
        }

        // ritorna l'oggetto in formato json

        try{
            $jsonCar = $serializer->serialize($car, 'json');
        }catch(\Exception $e){
            return new JsonResponse(['error' => 'Error serializing car: ' . $e->getMessage()], 500);
        }

        // return new Response($jsonCar, 200, ['Content-Type' => 'application/json']);

        return new JsonResponse(json_decode($jsonCar), 200, ['Content-Type' => 'application/json']);
    }


    // METODO CON DI EDIT
    #[Route('/car/edit/{id}', name: 'car_edit')]
    public function edit(EntityManagerInterface $entityManager, int $id, ValidatorInterface $validator){
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
        $car->setPrice(257.22);
        $car->setState(true); 
        $car->setIsNew(rand(0, 1) == 1); 
        $car->setCreatedAt(new \DateTime()); 
        $car->setUpdatedAt(new \DateTime()); 
        
        // Validazione dell'oggetto prima di essere salvato
        $errors = $validator->validate($car);

        if (count($errors) > 0) {
            return new JsonResponse(['error' => 'Validation failed', 'details' => (string) $errors], 400);
        }

        try{
            //  estratto dalla documentazione , è possibile richiamare il metodo persist(), ma non ce n'è il bisogno
            //  perchè Doctrine è già FOCUSSATO sull'oggeto interessato per l'update
            $entityManager->flush();
            return new JsonResponse(['success' => 'Car updated successfully', 'car_id' => $car->getId()], 200);

        }catch(\Exception $e){
            return new JsonResponse('Error. Cant Update Obj: ' . $e->getMessage());    
        }

        // return $this->redirectToRoute('car_show',['id' => $car->getId()]);
        return new JsonResponse(['success' => 'Car updated successfully', 'car_id' => $car->getId()], 200);

    }
    
    //METODO DELETE
    #[Route('/car/delete/{id}', name: 'car_delete')]
    public function delete(EntityManagerInterface $entityManager, int $id){
        $car = $entityManager->getRepository(Car::class)->find($id);

        //se eventualmente l'id non risulta presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        //HARD DELETE
        // $entityManager->remove($car);
        // $entityManager->flush();

        //SOFT DELETE
        try{
            $car->setDeletedAt(new \DateTime());
            $entityManager->flush();
        }catch(\Exception $e){
            return new JsonResponse(['error' => 'Error updating car: ' . $e->getMessage()], 500);    
        }
        // Successo: ritorna messaggio di successo
        // return new Response('Car deleted');
        return new JsonResponse(['success' => 'Car marked as deleted'], 200);


    }

    #[Route('/', name: 'car_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        //ritorna alla vista solo gli oggetti con valore di deletedAt => null

        try {
            $cars = $entityManager->getRepository(Car::class)->findBy(['deletedAt' => null]);

            // se il DB ha solo record con deletedAt != null (quindi eliminati) ritorna un messaggio
            if (empty($cars)) {
                return new JsonResponse(['message' => 'No cars available'], 200);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Could not retrieve cars: ' . $e->getMessage()], 500);
        }

        return $this->render('car/index.html.twig', [
            'controller_name' => 'CarController',
            'cars' => $cars
        ]);
    }

    //riempire database per test
    #[Route('/make_db', name: 'make_db')]
public function makeDb(EntityManagerInterface $entityManager, ValidatorInterface $validator)
{
    // Crea un array per contenere le auto
    $cars = [];

    //Piccolo array di modelli
    $models = [
        'BMW 1 Series',
        'BMW 2 Series',
        'BMW 3 Series',
        'BMW 4 Series',
        'BMW 5 Series',
        'BMW 6 Series',
        'BMW 7 Series',
        'BMW 8 Series',
        'BMW X1',
        'BMW X2',
        'BMW X3',
        'BMW X4',
        'BMW X5',
        'BMW X6',
        'BMW X7'
    ];
    
    // Esegui un ciclo per creare 15 auto
    for ($i = 0; $i < 15; $i++) {
        $newCar = new Car();
        $newCar->setBrand('BMW'); // Aggiungi una numerazione al brand
        $newCar->setModel($models[rand(0, 14)]); // Aggiungi una numerazione al modello
        $newCar->setPrice(rand(10000, 50000) / 100); // Prezzo casuale tra 100.00 e 500.00
        $newCar->setProductionYear(rand(2010, 2023)); // Anno di produzione casuale tra 2010 e 2023
        $newCar->setState(true); // Imposta lo stato a true (ad esempio "disponibile")
        $newCar->setIsNew(rand(0, 1) == 1); // Imposta se è nuova o usata casualmente
        $newCar->setCreatedAt(new \DateTime()); // Data di creazione
        $newCar->setUpdatedAt(new \DateTime()); // Data di aggiornamento

        // Aggiungo l'auto all'array
        $cars[] = $newCar;
    }

    try {
        foreach ($cars as $car) {
            $entityManager->persist($car); 
        }
        $entityManager->flush();
    } catch (\Exception $e) {
        // Gestisce eventuali errori durante il salvataggio
        return new Response('Error cant saving cars: ' . $e->getMessage());
    }

    // Successo: ritorna il messaggio
    return new JsonResponse(['success' => 'Saved 15 new Cars'], 201);
}

}
