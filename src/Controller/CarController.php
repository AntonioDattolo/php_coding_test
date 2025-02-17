<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Serializer\SerializerInterface;

// gestisce il listener per intercettare gli errori 
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
final class CarController extends AbstractController
{
    //METODO CREATE
    #[Route('/car/create', name: 'car_create', methods: ['POST'])]
    public function createCar(EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request)
    {
        $data = $request->request->all();

        //Controlla se i parametri principali sono null o vuoti
        if (empty($data['brand']) || empty($data['model']) || empty($data['price']) || empty($data['productionYear']) || empty($data['state']) || empty($data['isNew'])) {
            return new JsonResponse(['error' => 'Some required fields are missing or invalid. Please provide valid data.'], 400);
        }

        // Converte 'true' o 'false' come stringhe in booleano
        //testate con thunderclient, impostando true/false, ma symfony li riconosce come stringhe
        //dando errore di validazione "field must be boolean" 
        $state = filter_var($data['state'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $isNew = filter_var($data['isNew'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);


        $newCar = new Car();
        $newCar->setBrand($data['brand']);
        $newCar->setModel($data['model']);
        $newCar->setPrice($data['price']);
        $newCar->setProductionYear($data['productionYear']);
        $newCar->setState($state);
        $newCar->setIsNew($isNew);
        $newCar->setCreatedAt(new \DateTime());
        $newCar->setUpdatedAt(new \DateTime());
        // Validazione dell'oggetto prima di essere salvato
        $errors = $validator->validate($newCar);

        if (count($errors) > 0) {
            // Crea una lista di messaggi di errore
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            // Restituisci una risposta con gli errori in formato JSON
            return new JsonResponse(['error' => $errorMessages], 400);
        }

        try {
            $entityManager->persist($newCar);
            $entityManager->flush();
        } catch (\Exception $e) {
            // Gestisce eventuali errori durante il salvataggio
            return new Response('Error cant saving car: ' . $e->getMessage());
        }

        // Successo: ritorna il brand e il modello del nuovo oggetto creato
        return new JsonResponse(['success' => 'Saved new Car', 'car' => ['brand' => $newCar->getBrand(), 'model' => $newCar->getModel()]], 201);
    }


    // METODO SHOW
    #[Route('api/car/show/{id}', name: 'car_show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer)
    {
        $car = $entityManager->getRepository(Car::class)->find($id);

        //controllo se l'oggetto è presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
            // throw new NotFoundHttpException('Car not found');
        }

        // ritorna l'oggetto in formato json

        try {
            $jsonCar = $serializer->serialize($car, 'json');
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error serializing car: ' . $e->getMessage()], 500);
        }

        // return new Response($jsonCar, 200, ['Content-Type' => 'application/json']);

        return new JsonResponse(json_decode($jsonCar), 200, ['Content-Type' => 'application/json']);
    }


    // METODO CON DI EDIT
    #[Route('api/car/edit/{id}', name: 'car_edit', methods: ['PUT'])]
    public function edit(EntityManagerInterface $entityManager, int $id, ValidatorInterface $validator, Request $request)
    {   

        // Se i dati non sono validi, solleva un'eccezione
       
        $data = $request->request->all();

        //trova l'oggetto secondo l'id
        $car = $entityManager->getRepository(Car::class)->find($id);

        //controllo se l'oggetto è presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        } 

        // Verifica che tutti i parametri siano validi
        if (empty($data['brand']) || empty($data['model']) || empty($data['price']) || empty($data['productionYear'])) {
        return new JsonResponse(['error' => 'Some required fields are missing or invalid. Please provide valid data.'], 400);
        }

        $state = filter_var($data['state'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $isNew = filter_var($data['isNew'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $car->setBrand($data['brand']);
        $car->setModel($data['model']);
        $car->setPrice($data['price']);
        $car->setProductionYear($data['productionYear']);
        $car->setState($state);
        $car->setIsNew($isNew);
        // $car->setCreatedAt(new \DateTime()); data di creazione originale
        $car->setUpdatedAt(new \DateTime());

        // Validazione dell'oggetto prima di essere salvato
        $errors = $validator->validate($car);

        
            if (count($errors) > 0) {
                // Crea una lista di messaggi di errore
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                // Restituisci una risposta con gli errori in formato JSON
                return new JsonResponse(['error' => $errorMessages], 400);
            }

        try {
            //  estratto dalla documentazione , è possibile richiamare il metodo persist(), ma non ce n'è il bisogno
            //  perchè Doctrine è già FOCUSSATO sull'oggeto interessato per l'update
            $entityManager->flush();
            return new JsonResponse(['success' => 'Car updated successfully', 'car_id' => $car->getId()], 200);
        } catch (\Exception $e) {
            return new JsonResponse('Error. Cant Update Obj: ' . $e->getMessage());
        }

        // return $this->redirectToRoute('car_show',['id' => $car->getId()]);
        return new JsonResponse(['success' => 'Car updated successfully', 'car_id' => $car->getId()], 200);
    }

    //METODO DELETE
    #[Route('api/car/delete/{id}', name: 'car_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id)
    {

        $car = $entityManager->getRepository(Car::class)->find($id);

        //se eventualmente l'id non risulta presente nel db
        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        //HARD DELETE
        // $entityManager->remove($car);
        // $entityManager->flush();

        //SOFT DELETE
        try {
            $car->setDeletedAt(new \DateTime());
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error updating car: ' . $e->getMessage()], 500);
        }
        // Successo: ritorna messaggio di successo
        // return new Response('Car deleted');
        return new JsonResponse(['success' => 'Car marked as deleted'], 200);
    }

    //Per avere la lista del db nel server di sviluppo
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
