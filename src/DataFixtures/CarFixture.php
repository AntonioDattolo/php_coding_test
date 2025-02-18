<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Car;

class CarFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        $brands = ['Toyota', 'Ford', 'BMW', 'Audi', 'Mercedes'];
        $models = ['Corolla', 'Focus', 'X5', 'A4', 'C-Class'];

        for ($i = 0; $i < 150; $i++) {
            
            $car = new Car();
            $car->setBrand($brands[array_rand($brands)]); // Marca casuale
            $car->setModel($models[array_rand($models)]); // Modello casuale
            $car->setPrice(rand(20000, 80000)); // Prezzo casuale tra 20.000 e 80.000
            $car->setProductionYear(rand(1990, 2023)); // Anno di produzione casuale tra 2010 e 2023
            //  var_dump($car->setState((bool)(rand(0, 1) === 1 ? true : false))) ;// Stato casuale (true o false)
            $car->setState(rand(0, 1) === 1 ? true : false);
            $car->setIsNew(rand(0, 1) === 1 ? true : false); // Nuovo o usato casuale (true o false)

            $manager->persist($car);
            $manager->flush();
        }

    }
}
