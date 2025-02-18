<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;

/* 
200 => ok
201 => created
400 => bad request
404 => not found
500 => internal server
*/


class CarControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        //Crea un client HTTP simulato
        $this->client = static::createClient();

        //recupero la classe speciale
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        //svuoto e preparo il DB
        $this->entityManager->createQuery('DELETE FROM App\Entity\Car')->execute();
        
    }

    //********************************************************************Gestione codice di stato 201 (Created)
    public function testCreateCar(): void
    {
        $this->client->request('POST', '/car/create', [
            'brand' => 'BMW',
            'model' => 'X5',
            'price' => 55000,
            'productionYear' => 2022,
            'state' => 'true',
            'isNew' => 'true',
        ]);
        //verifica codice di riposta 201
        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        //JsonResponse(['success' => 'Saved new Car', 'car' => ['brand' => $newCar->getBrand(), 'model' => $newCar->getModel()]], 201)
        //verifica la key dell'array => success
        $this->assertArrayHasKey('success', $responseData);
        //verifica che alla key dell'array corrisponda il valore 'Saved new Car'
        $this->assertEquals('The new car has been saved successfully.', $responseData['success']);
    }

    //********************************************************************Gestione codice di stato 400 (Bad Request)
    public function testCreateCarWithInvalidFields(): void
    {
        $this->client->request('POST', '/car/create', [
            'brand' => 'B',  // brand troppo corto (1 lettera)
            'model' => 'X5',
            'price' => 55000,
            'productionYear' => 2022,
            'state' => 'true',
            'isNew' => 'true',
        ]);

        // verifica che la risposta abbia codice di stato 400 (Bad Request)
        $this->assertResponseStatusCodeSame(400);

        // Decodifica la risposta JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertArrayHasKey('error', $responseData);
        $this->assertNotEmpty($responseData['error']);
        $this->assertStringContainsString('The brand must be a string characters long min 2 and max 50.', $responseData['error']);  // Verifica che il messaggio d'errore sia corretto

        //********************************************************************Gestione codice di stato 400 (Bad Request) 
        $this->client->request('POST', '/car/create', [
            'brand' => 'BMW',
            'model' => 'X', // brand troppo corto (1 lettera)
            'price' => 55000,
            'productionYear' => 2022,
            'state' => 'true',
            'isNew' => 'true',
        ]);

        // verifica che la risposta abbia codice di stato 400 (Bad Request)
        $this->assertResponseStatusCodeSame(400);

        // Decodifica la risposta JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertArrayHasKey('error', $responseData);
        $this->assertNotEmpty($responseData['error']);
        $this->assertStringContainsString('The model must be a string characters long min 2 and max 50.', $responseData['error']);  // Verifica che il messaggio d'errore sia corretto

        //********************************************************************Gestione codice di stato 400 (Bad Request)
        //valore negativo campo 'price'

        $this->client->request('POST', '/car/create', [
            'brand' => 'BMW',
            'model' => 'M2',
            'price' => -1, // prezzo negativo
            'productionYear' => 2022,
            'state' => 'true',
            'isNew' => 'true',
        ]);

        // verifica che la risposta abbia codice di stato 400 (Bad Request)
        $this->assertResponseStatusCodeSame(400);

        // Decodifica la risposta JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertArrayHasKey('error', $responseData);
        $this->assertNotEmpty($responseData['error']);
        $this->assertStringContainsString("The price must be positive.", $responseData['error']);  // Verifica che il messaggio d'errore sia corretto
    }

    //********************************************************************Gestione Codice di stato 405

    public function testMethodAllowed(){
        //invece del metodo POST invia richiesta con metodo GET
        $this->client->request('GET', '/car/create', [
            'brand' => 'BMW',
            'model' => 'M2',
            'price' => 55000, 
            'productionYear' => 2022,
            'state' => 'true',
            'isNew' => 'true',
        ]);

        // verifica che la risposta abbia codice di stato 400 (Bad Request)
        $this->assertResponseStatusCodeSame(405);

        // Decodifica la risposta JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertArrayHasKey('error', $responseData);
        $this->assertNotEmpty($responseData['error']);
        $this->assertStringContainsString("Method Not Allowed (Allow: POST)", $responseData['error']);  // Verifica che il messaggio d'errore sia corretto
    }

    //********************************************************************Inizio test rotta Show

    public function testShowRoute(){
        // recupero la classe speciale entityManagerInterface
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        //mi connetto al db di test
        $connection = $this->entityManager->getConnection();
        //resetto il conteggio dell'id
        $connection->executeStatement('ALTER TABLE cars AUTO_INCREMENT = 1');

        //Creo array per il salvataggio di un dato
        $data = [
            'brand' => 'BMW',
            'model' => 'M2',
            'price' => 55000,
            'productionYear' => 2022,
            'state' => true,
            'isNew' => true,
        ];

        // creo un record di test e la salvo nel database
        $car = new Car();
        $car->setBrand('BMW');
        $car->setModel('M2');
        $car->setPrice(55000);
        $car->setProductionYear(2022);
        $car->setState(true);
        $car->setIsNew(true);

        $entityManager->persist($car);
        $entityManager->flush();
    
        //********************************************************************Gestione codice di stato 200 (DB con almeno un record)
        $this->client->request('GET', 'api/car/show/1' , []);

        // verifica che la risposta abbia codice di stato 400 (Bad Request)
        $this->assertResponseStatusCodeSame(200);

        // Decodifica la risposta JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        //rimuovo campi dinamici come ID, 'createdAt', 'updateAt', 'deletedAt'
        $filteredResponse = array_filter($responseData, function($key){
            return !in_array($key,['id', 'createdAt', 'updatedAt', 'deletedAt']);
        },ARRAY_FILTER_USE_KEY);

        // confrontiamo i dati del response con i dati inviati
        $this->assertEquals($data, $filteredResponse);

        //********************************************************************Gestione codice di stato 404 (record non presente)
        $this->client->request('GET', '/api/car/show/9999', []);

        // verifica che la risposta abbia codice di stato 404 (not found)
        $this->assertResponseStatusCodeSame(404);
    
        // Decodifica la risposta JSON
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
    
        $this->assertArrayHasKey('error', $responseData);
        $this->assertNotEmpty($responseData['error']);
        $this->assertStringContainsString("Error 404: Car not found.  Please check the ID and try again.", $responseData['error']);  // Verifica che il messaggio d'errore sia corretto
    
    }
    //********************************************************************Test rotta Edit

    public function testEditRecord(){
        // recupero la classe speciale entityManagerInterface
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        //mi connetto al db di test
        $connection = $this->entityManager->getConnection();
        //resetto il conteggio dell'id
        $connection->executeStatement('ALTER TABLE cars AUTO_INCREMENT = 1');

        //Creo array per il salvataggio di un dato
        $data = [
            'brand' => 'BMW',
            'model' => 'M2',
            'price' => 55000,
            'productionYear' => 2022,
            'state' => true,
            'isNew' => true,
        ];

        // creo un record di test e la salvo nel database
        $car = new Car();
        $car->setBrand('BMW');
        $car->setModel('M2');
        $car->setPrice(55000);
        $car->setProductionYear(2022);
        $car->setState(true);
        $car->setIsNew(true);

        $entityManager->persist($car);
        $entityManager->flush();

        //********************************************************************Gestione codice 201 in edit
        $this->client->request('PUT', '/api/car/edit/1', [
             'brand' => 'BMW',
            'model' => 'PROVA EDIT',
            'price' => 55000,
            'productionYear' => 2022,
            'state' => true,
            'isNew' => true,
        ]);
        //verifica codice di riposta 200
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        //verifica la key dell'array => success
        $this->assertArrayHasKey('success', $responseData);

        //verifica che alla key dell'array corrisponda il valore 'Car updated successfully'
        $this->assertEquals("The car has been updated successfully", $responseData['success']);

        //********************************************************************Gestione codice 404 in edit

        $this->client->request('PUT', '/api/car/edit/9999', [
            'brand' => 'BMW',
           'model' => 'PROVA EDIT',
           'price' => 55000,
           'productionYear' => 2022,
           'state' => true,
           'isNew' => true,
       ]);
       //verifica codice di riposta 200
       $this->assertResponseStatusCodeSame(404);

       $responseData = json_decode($this->client->getResponse()->getContent(), true);

       //verifica la key dell'array => success
       $this->assertArrayHasKey('error', $responseData);

       //verifica che alla key dell'array corrisponda il valore 'Car updated successfully'
       $this->assertEquals("Error 404: Car not found.  Please check the ID and try again.", $responseData['error']);

       //********************************************************************Gestione codice 400 in edit (brand non valido)

       $this->client->request('PUT', '/api/car/edit/1', [
        'brand' => 'B', // brand non valido
       'model' => 'PROVA EDIT',
       'price' => 55000,
       'productionYear' => 2022,
       'state' => true,
       'isNew' => true,
        ]);
        //verifica codice di riposta 200
        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        //verifica la key dell'array => success
        $this->assertArrayHasKey('error', $responseData);

        //verifica che alla key dell'array corrisponda il valore 'Car updated successfully'
        $this->assertEquals("The brand must be a string characters long min 2 and max 50.", $responseData['error']);

    }
    
    //********************************************************************Test rotta Delete

    public function testSoftDelete(){
        // recupero la classe speciale entityManagerInterface
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        //mi connetto al db di test
        $connection = $this->entityManager->getConnection();
        //resetto il conteggio dell'id
        $connection->executeStatement('ALTER TABLE cars AUTO_INCREMENT = 1');

        //Creo array per il salvataggio di un dato
        $data = [
            'brand' => 'BMW',
            'model' => 'M2',
            'price' => 55000,
            'productionYear' => 2022,
            'state' => true,
            'isNew' => true,
        ];

        // creo un record di test e la salvo nel database
        $car = new Car();
        $car->setBrand('BMW');
        $car->setModel('M2');
        $car->setPrice(55000);
        $car->setProductionYear(2022);
        $car->setState(true);
        $car->setIsNew(true);

        $entityManager->persist($car);
        $entityManager->flush();

        $this->client->request('DELETE', '/api/car/delete/1');

        $this->assertResponseStatusCodeSame(200);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
    
        $this->assertArrayHasKey('success', $responseData);

        $this->assertEquals("The Car marked has been deleted.", $responseData['success']);

        //********************************************************************Gestione codice errore 404

        $this->client->request('DELETE', '/api/car/delete/99999');

        $this->assertResponseStatusCodeSame(404);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
    
        $this->assertArrayHasKey('error', $responseData);

        $this->assertEquals("Error 404: Car not found.  Please check the ID and try again.", $responseData['error']);



    }
    
}
