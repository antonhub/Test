<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         $country = new Country();
         $country->setName('Ukraine');
         $country->setAlpha2('UA');
         $country->setNumCode('380');
         $country->setEu(false);
         $manager->persist($country);

         $country = new Country();
         $country->setName('Netherlands');
         $country->setAlpha2('NL');
         $country->setNumCode('031');
         $country->setEu(true);
         $manager->persist($country);

         $country = new Country();
         $country->setName('Russia');
         $country->setAlpha2('RU');
         $country->setNumCode('007');
         $country->setEu(null);
         $manager->persist($country);

        $manager->flush();
    }
}
