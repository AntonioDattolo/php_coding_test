<?php
//***********************************N. B.
//I test unitari sono progettati per verificare il corretto funzionamento di singole unità di codice,
//come metodi o classi, in isolamento dal resto del sistema.
//****************************************** 
use PHPUnit\Framework\TestCase;
use App\Entity\Car;

class UnitTest extends TestCase
{
    // 1 test
    public function testSetAndGetBrand()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Imposta un valore corretto per il brand
        $car->setBrand('MERCEDES');

        // Verifica che il valore impostato per il brand sia quello atteso
        $this->assertEquals('MERCEDES', $car->getBrand());
    }
    // 2 test
    public function testBrandCantBeNull()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Diciamo a PHPUnit di aspettarsi un'eccezione di tipo InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Diciamo che l'eccezione deve avere questo messaggio
        $this->expectExceptionMessage("Brand can't be nullable");

        // Passa una stringa vuota per testare la validazione del brand
        $car->setBrand('');
    }
    // 3 test
    public function testSetAndGetModel()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Imposta il valore per il modello
        $car->setModel('CLASSE C');

        // Verifica che il modello impostato corrisponda al valore atteso
        $this->assertEquals('CLASSE C', $car->getModel());
    }
    // 4 test
    public function testModelCantBeNull()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Diciamo a PHPUnit di aspettarsi un'eccezione di tipo InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Diciamo che l'eccezione deve avere questo messaggio
        $this->expectExceptionMessage("Model can't be nullable");

        // Passa una stringa vuota per testare la validazione del brand
        $car->setModel('');
    }
    // 5 test
    public function testSetAndGetPrice()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Imposta il valore del prezzo
        $car->setPrice(30000);

        // Verifica che il prezzo impostato corrisponda al valore atteso
        $this->assertEquals(30000, $car->getPrice());
    }
    // 6 test
    public function testPricecCantBeNegative()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Diciamo a PHPUnit di aspettarsi un'eccezione di tipo InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Diciamo che l'eccezione deve avere questo messaggio
        $this->expectExceptionMessage("The price must be positive.");

        // Passa una stringa vuota per testare la validazione del brand
        $car->setPrice(-1.00);
    }
    // 7 test
    public function testSetAndGetProductionYear()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Imposta l'anno di produzione
        $car->setProductionYear(2022);

        // Verifica che l'anno di produzione impostato corrisponda al valore atteso
        $this->assertEquals(2022, $car->getProductionYear());
    }
    // 8 test
    public function testProdYearValidation()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Diciamo a PHPUnit di aspettarsi un'eccezione di tipo InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Diciamo che l'eccezione deve avere questo messaggio
        $this->expectExceptionMessage("The production year must be positive and cannot be greater than the current year.");

        // Passa un anno di produzione negativo per testare la validazione
        $car->setProductionYear(-2020);

        // Passa un anno di produzione maggiore dell'attuale anno
        $car->setProductionYear(2030);

    }
    // 9 test
    public function testSetAndGetState()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Imposta lo stato del veicolo
        $car->setState(true);

        // Verifica che lo stato impostato sia vero
        $this->assertTrue($car->getState());
    }
    // 10 test
    public function testStateValidation()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Diciamo a PHPUnit di aspettarsi un'eccezione di tipo InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Diciamo che l'eccezione deve avere questo messaggio
        $this->expectExceptionMessage("Specify the status of the car (sold/available).");

        // Passa un valore null
        $car->setState(null);


    }
    // 11 test
    public function testSetAndGetIsNew()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Imposta il valore per il campo 'isNew'
        $car->setIsNew(true);

        // Verifica che 'isNew' sia impostato su true
        $this->assertTrue($car->getIsNew());
    }
    // 12 test
    public function testIsNewValidation(){
        //Crea un'istanza della classe Car
        $car = new Car();
        
        // Diciamo a PHPUnit di aspettarsi un'eccezione di tipo InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);

        // Diciamo che l'eccezione deve avere questo messaggio
        $this->expectExceptionMessage("Specify the condition of the car (new/used).");

        //Passo un valore null
        $car->setIsNew(null);
    }
    // 13 test
    public function testSetAndGetCreatedAt()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Crea una nuova data e imposta il campo 'createdAt'
        $date = new \DateTime();
        $car->setCreatedAt($date);

        // Verifica che 'createdAt' sia un'istanza di DateTime
        $this->assertInstanceOf(\DateTime::class, $car->getCreatedAt());

        // Verifica che la data impostata corrisponda alla data attesa
        $this->assertEquals($date, $car->getCreatedAt());
    }
    // 14 test
    public function testSetAndGetUpdatedAt()
    {
        // Crea un'istanza della classe Car
        $car = new Car();

        // Crea una nuova data e imposta il campo 'updatedAt'
        $date = new \DateTime();
        $car->setUpdatedAt($date);

        // Verifica che 'updatedAt' sia un'istanza di DateTime
        $this->assertInstanceOf(\DateTime::class, $car->getUpdatedAt());

        // Verifica che la data impostata corrisponda alla data attesa
        $this->assertEquals($date, $car->getUpdatedAt());
    }

    //Output desiderato : successo con numero di test(14)
}
