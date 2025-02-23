<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use OpenApi\Attributes as OA;

final class SearchController extends AbstractController
{
    #[Route('/api/car/search', name: 'car_search', methods: ['GET'])]

    #[OA\Get(
        path: "/api/car/search",
        summary: "Search cars by brand, price range, and state",
        parameters: [
            new OA\Parameter(
                name: "brand",
                in: "query",
                description: "The brand of the car to search for",
                required: true,
                schema: new OA\Schema(type: "string", example: "BMW")
            ),
            new OA\Parameter(
                name: "min",
                in: "query",
                description: "The minimum price of the car",
                required: true,
                schema: new OA\Schema(type: "number", example: 5000)
            ),
            new OA\Parameter(
                name: "max",
                in: "query",
                description: "The maximum price of the car",
                required: true,
                schema: new OA\Schema(type: "number", example: 50000)
            ),
            new OA\Parameter(
                name: "state",
                in: "query",
                description: "The state of the car (true for new, false for used)",
                required: true,
                schema: new OA\Schema(type: "boolean", example: true)
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "The page number for pagination",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cars found",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "totalResult", type: "integer", example: 10),
                        new OA\Property(property: "totalPage", type: "integer", example: 3),
                        new OA\Property(property: "actualPage", type: "integer", example: 1),
                        new OA\Property(
                            property: "result",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "brand", type: "string", example: "BMW"),
                                    new OA\Property(property: "model", type: "string", example: "X5"),
                                    new OA\Property(property: "price", type: "number", example: 45000.00),
                                    new OA\Property(property: "state", type: "boolean", example: true)
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid input or missing fields"
            ),
            new OA\Response(
                response: 404,
                description: "Page not found"
            ),
            new OA\Response(
                response: 500,
                description: "Internal server error"
            )
        ]
    )]

    public function searchCar(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer)
    {
        try {
            $brand = $request->query->get('brand');
            $minPrice = $request->query->get('min');
            $maxPrice = $request->query->get('max');
            $state = filter_var($request->query->get('state'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            //eventuale paginazione
            $pageLimit = 3; //default per i pochi risultati derivanti dal ricercare 'BMW'
            $page = $request->query->get('page');

            $data = [
                'brand' => $brand,
                'min' => $minPrice,  // Prezzo minimo
                'max' => $maxPrice,  // Prezzo massimo
                'state' => $state,
                'page' => $page
            ];

            //Controlla se i parametri principali sono null o vuoti
            $missingFields = [];
            $requiredFields = ['brand', 'max', 'min', 'state',];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }

            // Restituisce un errore con i campi mancanti
            if (count($missingFields) > 0) {
                return new JsonResponse(['error' => 'Error 400 - Missing required fields: ' . implode(', ', $missingFields)], 400);
            }

            //Validazione brand
            if ((strlen($data['brand']) < 2 || strlen($data['brand']) > 50)) {

                return new JsonResponse(['error' => 'Error 400 : The brand must be a string characters long min 2 and max 50.'], 400);
            }

            // Validazione parametri numerici per i prezzi
            if (!is_numeric($data['min']) || !is_numeric($data['max'])) {
                return new JsonResponse(['error' => 'Error 400 : Price parameters must be numeric.'], 400);
            }

            // Validazione stato
            if ($state === null) {
                return new JsonResponse(['error' => 'Error 400 : Invalid value for state. Expected true or false.'], 400);
            }

            //validazione pagina N.B. di default dovrebbe essere impostata a 1
            if ($page <= 0) {
                return new JsonResponse(['error' => 'Error 400 : Invalid value for page. Expected value greater than zero.'], 400);
            }

            //calcoliamo l'offsett per la query
            $pageNumber = ($page - 1 ) * $pageLimit; // l'offesett altrimenti partirebbe da uno e non da 0.

            //SQL query
            //SELECT COUNT(cars.id) FROM cars WHERE cars.brand LIKE '%BMW%' AND cars.price BETWEEN 5000 AND 50000 AND cars.state = 1;

            $cars = $entityManager->createQueryBuilder()
                ->select('cars')
                ->from(Car::class, 'cars')
                ->where('cars.brand LIKE :brand')
                ->andWhere('cars.price BETWEEN :minPrice AND :maxPrice')
                ->andWhere('cars.state = :state')
                ->setParameter('brand', '%' . $data['brand'] . '%') // Aggiungi % per cercare in qualsiasi parte della stringa
                ->setParameter('minPrice', $data['min'])
                ->setParameter('maxPrice', $data['max'])
                ->setParameter('state', $data['state'])
                ->orderBy('cars.price', 'ASC')
                ->setFirstResult($pageNumber) //setta da quale risultato far iniziare la pagina
                ->setMaxResults($pageLimit) //setta il numero massimo di risultati per pagina
                ->getQuery()
                ->getResult();

            //calcolo totale dei risultati senza paginazione
            $totalResult = $entityManager->createQueryBuilder()
                ->select('cars')
                ->from(Car::class, 'cars')
                ->where('cars.brand LIKE :brand')
                ->andWhere('cars.price BETWEEN :minPrice AND :maxPrice')
                ->andWhere('cars.state = :state')
                ->setParameter('brand', '%' . $data['brand'] . '%') // Aggiungi % per cercare in qualsiasi parte della stringa
                ->setParameter('minPrice', $data['min'])
                ->setParameter('maxPrice', $data['max'])
                ->setParameter('state', $data['state'])
                ->orderBy('cars.price', 'ASC')
                ->getQuery()
                ->getResult();

            //ulteriore validazione di paginazione

            $jsonCar = $serializer->serialize($cars, 'json');

            $jsonTotalResult = $serializer->serialize($totalResult, 'json');

            if ($page > count(json_decode($jsonTotalResult)) / $pageLimit) {
                return new JsonResponse(['error' => 'Error 404 : Invalid value for page. Page not found.'], 404);
            }

            return new JsonResponse(
                [
                    'totalResult' => count(json_decode($jsonTotalResult)),
                    'totalPage' => ceil(count($totalResult) / $pageLimit), // totale risultati trovati per pagina
                    'actualPage' => $page,
                    'result' => json_decode($jsonCar),
                ],
                200, // codice di stato
            );
            
        } catch (\Exception $e) {
            // Gestisce eventuali errori generici
            return new JsonResponse(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
