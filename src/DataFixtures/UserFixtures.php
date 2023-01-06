<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $admin = new Client();
        $admin->setName("admin");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));
        $manager->persist($admin);

        $client1 = new Client();
        $client1->setName("client1");
        $client1->setRoles(["ROLE_CLIENT"]);
        $client1->setPassword($this->userPasswordHasher->hashPassword($client1, "password"));
        $manager->persist($client1);
        for ($i = 0; $i < 5; $i++) {
            $customer = new Customer();
            $customer->setEmail($client1->getName() . '.customer'. $i . '@gmail.com');
            $customer->setFirstName($client1->getName() . '\'s customer ' . $i);
            $customer->setLastName('Doe');
            $customer->setRoles(["ROLE_USER"]);
            $customer->setClient($client1);
            $manager->persist($customer);
        }

        $client2 = new Client();
        $client2->setName("client2");
        $client2->setRoles(["ROLE_CLIENT"]);
        $client2->setPassword($this->userPasswordHasher->hashPassword($client2, "password"));
        $manager->persist($client2);
        for ($i = 0; $i < 5; $i++) {
            $customer = new Customer();
            $customer->setEmail($client2->getName() . '.customer'. $i . '@gmail.com');
            $customer->setFirstName($client2->getName() . '\'s customer ' . $i);
            $customer->setLastName('Doe');
            $customer->setRoles(["ROLE_USER"]);
            $customer->setClient($client2);
            $manager->persist($customer);
        }

        $client3 = new Client();
        $client3->setName("client3");
        $client3->setRoles(["ROLE_CLIENT"]);
        $client3->setPassword($this->userPasswordHasher->hashPassword($client3, "password"));
        $manager->persist($client3);
        for ($i = 0; $i < 5; $i++) {
            $customer = new Customer();
            $customer->setEmail($client3->getName() . '.customer'. $i . '@gmail.com');
            $customer->setFirstName($client3->getName() . '\'s customer ' . $i);
            $customer->setLastName('Doe');
            $customer->setRoles(["ROLE_USER"]);
            $customer->setClient($client3);
            $manager->persist($customer);
        }

        $manager->flush();
    }
}
