<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use OpenApi\Attributes as OA;
final class CarController extends AbstractController
{
    //**************************************METODO CREATE

    #[Route('/api/car/create', name: 'car_create', methods: ['POST'])]
    
    #[OA\Post(
        path: "/api/car/create",
        summary: "Create a new car",
        requestBody: new OA\RequestBody(
            description: "Brand, Model, Year, Price, State, isNew",
            required: true),
        responses: [
            new OA\Response(
                response: 201,
                description: 'The new car has been saved successfully.',
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "model", type: "string", example: "Tesla Model S"),
                    ])
            )
        ]
    )]
    public function createCar(EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request)
    {
        $data = $request->request->all();


        //Controlla se i parametri principali sono null o vuoti
        $missingFields = [];
        $requiredFields = ['brand', 'model', 'price', 'productionYear', 'state', 'isNew'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        // Restituisce un errore con i campi mancanti
        if (count($missingFields) > 0) {
            return new JsonResponse(['error' => 'Error 404 - Missing required fields: ' . implode(', ', $missingFields)], 400);
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
        try {
            $errors = $validator->validate($newCar);
            if (count($errors) > 0) {
                // Crea una lista di messaggi di errore
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                // Restituisce una risposta con gli errori in formato JSON
                return new JsonResponse(['error' => $errorMessages], 400);
            }

            $entityManager->persist($newCar);
            $entityManager->flush();
        } catch (\Exception $e) {

            // Gestisce eventuali errori durante il salvataggio
            return new JsonResponse(['error' => 'Internal server Error: ' . $e->getMessage()], 500);
        }

        // Successo: ritorna il brand e il modello del nuovo oggetto creato
        return new JsonResponse(['success' => 'The new car has been saved successfully.', 'car' => ['brand' => $newCar->getBrand(), 'model' => $newCar->getModel()]], 201);
    }

    //********************************* METODO SHOW

    #[Route('api/car/show/{id}', name: 'car_show', methods: ['GET'])]
    
    #[OA\Get(
        path: "/api/car/show/{id}",
        summary: "Get a car by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "The ID of the car to retrieve",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Car found",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "brand", type: "string", example: "Tesla"),
                        new OA\Property(property: "model", type: "string", example: "Model S"),
                        new OA\Property(property: "price", type: "number", example: 55257.00),
                        new OA\Property(property: "productionYear", type: "integer", example: 2023),
                        new OA\Property(property: "state", type: "boolean", example: true),
                        new OA\Property(property: "isNew", type: "boolean", example: false)
                    ]
                )
            )
        ]
    )]

    public function show(EntityManagerInterface $entityManager, int $id, SerializerInterface $serializer)
    {
        $car = $entityManager->getRepository(Car::class)->findOneBy(['id' => $id, 'deletedAt' => null]);

        //controllo se l'oggetto è presente nel db

        if (!$car) {
            // return new Response('Error 404: Car not found.  Please check the ID and try again.', Response::HTTP_NOT_FOUND);
            return new JsonResponse(['error' => 'Error 404: Car not found.  Please check the ID and try again.'], 404);
            // throw new NotFoundHttpException('Error 404: Car not found.  Please check the ID and try again.');
        }

        try {
            // ritorna l'oggetto in formato json se presente
            $jsonCar = $serializer->serialize($car, 'json');
            // return new Response($jsonCar, 200, ['Content-Type' => 'application/json']);
            return new JsonResponse(json_decode($jsonCar), 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal server Error: ' . $e->getMessage()], 500);
        }
    }

    //********************************* METODO EDIT

    #[Route('api/car/edit/{id}', name: 'car_edit', methods: ['PUT'])]
    
    #[OA\Put(
        path: "/api/car/edit/{id}",
        summary: "Update a car by ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "The ID of the car to update",
                required: true,
                schema: new OA\Schema(type: "integer")
    )],
        requestBody: new OA\RequestBody(
            description: "Updated car data",
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "brand", type: "string", example: "Tesla"),
                    new OA\Property(property: "model", type: "string", example: "Model S"),
                    new OA\Property(property: "price", type: "number", example: 55257.00),
                    new OA\Property(property: "productionYear", type: "integer", example: 2023),
                    new OA\Property(property: "state", type: "boolean", example: true),
                    new OA\Property(property: "isNew", type: "boolean", example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Car updated successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "string", example: "The car has been updated successfully"),
                        new OA\Property(property: "car_id", type: "integer", example: 1)
                    ]
                )
            ),    
        ]
    )]

    public function edit(EntityManagerInterface $entityManager, int $id, ValidatorInterface $validator, Request $request)
    {
        // Se i dati non sono validi, solleva un'eccezione
        $data = $request->request->all();

        //trova l'oggetto secondo l'id
        $car = $entityManager->getRepository(Car::class)->findOneBy(['id' => $id, 'deletedAt' => null]);

        //controllo se l'oggetto è presente nel db
        if (!$car) {
            return new JsonResponse(['error' => 'Error 404: Car not found.  Please check the ID and try again.'], 404);
        }

        // Verifica che tutti i parametri siano validi
        if (empty($data['brand']) || empty($data['model']) || empty($data['price']) || empty($data['productionYear']) || empty($data['state']) || empty($data['isNew'])) {
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
        try {
            if (count($errors) > 0) {
                // Crea una lista di messaggi di errore
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                // Restituisce una risposta con gli errori in formato JSON
                return new JsonResponse(['error' => $errorMessages], 400);
            }
            //  estratto dalla documentazione , è possibile richiamare il metodo persist(), ma non ce n'è il bisogno
            //  perchè Doctrine è già FOCUSSATO sull'oggetto interessato per l'update
            $entityManager->flush();

            return new JsonResponse(['success' => 'The car has been updated successfully', 'car_id' => $car->getId()], 200);
        } 
        catch (\Exception $e) {

            return new JsonResponse('Internal server Error: ' . $e->getMessage(), 500);
        }
        
    }

    //**************************METODO DELETE
    #[Route('api/car/delete/{id}', name: 'car_delete', methods: ['DELETE'])]
    
    #[OA\Delete(
        path: "/api/car/delete/{id}",
        summary: "Delete a car by ID (soft delete)",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "The ID of the car to delete",
                required: true,
                schema: new OA\Schema(type: "integer")
        )],
        responses: [
            new OA\Response(
                response: 200,
                description: "Car marked as deleted",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "string", example: "The Car has been marked as deleted."),
                        new OA\Property(property: "deletedAt", type: "string", format: "date-time", example: "2023-10-01T12:34:56Z")
                    ]
                )
            ),
        ]
    )]

    public function delete(EntityManagerInterface $entityManager, int $id)
    {

        $car = $entityManager->getRepository(Car::class)->find($id);

        //se eventualmente l'id non risulta presente nel db
        if (!$car) {

            return new JsonResponse(['error' => 'Error 404: Car not found.  Please check the ID and try again.'], 404);
        }

        //HARD DELETE
        // $entityManager->remove($car);
        // $entityManager->flush();

        //SOFT DELETE
        try {
            // setto la data della cancellazzione da null a 'Date/time/
            $car->setDeletedAt(new \DateTime());
            $entityManager->flush();
        } catch (\Exception $e) {

            return new JsonResponse(['error' => 'Internal server Error: ' . $e->getMessage()], 500);
        }

        // Successo: ritorna messaggio di successo
        // return new Response('Car deleted');
        return new JsonResponse(['success' => 'The Car marked has been deleted.', 'deletedAt' => $car->getDeletedAt()], 200);
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
}
