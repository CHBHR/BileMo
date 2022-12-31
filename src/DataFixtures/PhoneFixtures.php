<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PhoneFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $phone1 = new Phone();
        $phone1->setName('Samsung Galaxy Z Flip 3 Black');
        $phone1->setBrand('Samsung');
        $phone1->setOs('Android 11');
        $phone1->setScreenSize('6,7"');
        $manager->persist($phone1);

        $phone2 = new Phone();
        $phone2->setName('Google Pixel 6 Pro Noir');
        $phone2->setBrand('Google');
        $phone2->setOs('Android 12');
        $phone2->setScreenSize('6,7"');
        $manager->persist($phone2);

        $phone3 = new Phone();
        $phone3->setName('Xiaomi 11T Bleu');
        $phone3->setBrand('Xiaomi');
        $phone3->setOs('Android 11');
        $phone3->setScreenSize('6,6"');
        $manager->persist($phone3);

        $phone4 = new Phone();
        $phone4->setName('XIAOMI Redmi Note 10 Pro Gris');
        $phone4->setBrand('Xiaomi');
        $phone4->setOs('Android 10');
        $phone4->setScreenSize('6,6"');
        $manager->persist($phone4);

        $phone5 = new Phone();
        $phone5->setName('Samsung Galaxy A52S Noir');
        $phone5->setBrand('Samsung');
        $phone5->setOs('Android 11');
        $phone5->setScreenSize('6,5"');
        $manager->persist($phone5);

        $phone6 = new Phone();
        $phone6->setName('iPhone 11 Noir');
        $phone6->setBrand('Apple');
        $phone6->setOs('iOS 13');
        $phone6->setScreenSize('6,1"');
        $manager->persist($phone6);

        $phone7 = new Phone();
        $phone7->setName('iPhone 14 Noir');
        $phone7->setBrand('Apple');
        $phone7->setOs('iOS 16');
        $phone7->setScreenSize('6,1"');
        $manager->persist($phone7);

        $phone8 = new Phone();
        $phone8->setName('ASUS Zenfone 9 Rouge');
        $phone8->setBrand('Asus');
        $phone8->setOs('Android 11');
        $phone8->setScreenSize('5,9"');
        $manager->persist($phone8);

        $phone9 = new Phone();
        $phone9->setName('ASUS ROG Phone 6 Blanc');
        $phone9->setBrand('Asus');
        $phone9->setOs('Android 12');
        $phone9->setScreenSize('6,7"');
        $manager->persist($phone9);

        $phone10 = new Phone();
        $phone10->setName('OPPO Find X5 Pro Noir');
        $phone10->setBrand('OPPO');
        $phone10->setOs('Android 12');
        $phone10->setScreenSize('6,7"');
        $manager->persist($phone10);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GroupeFixtures::class,
        ];
    }
    
}