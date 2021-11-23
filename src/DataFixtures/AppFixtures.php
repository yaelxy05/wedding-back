<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Guest;
use App\Entity\Provider;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Provider\ApoWeddingProvider;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Connection $connection)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->connection = $connection;
    }
    
    const NB_GUEST = 20;
    const NB_PROVIDER = 20;
    const NB_TASK = 15;
    const NB_USER = 20;

    private function truncate()
    {
        // disabling constraints FK 
        $users = $this->connection->query('SET foreign_key_checks = 0');
        // We truncate
        $users = $this->connection->query('TRUNCATE TABLE guest');
        $users = $this->connection->query('TRUNCATE TABLE provider');
        $users = $this->connection->query('TRUNCATE TABLE task');
        $users = $this->connection->query('TRUNCATE TABLE user');
        $users = $this->connection->query('TRUNCATE TABLE provider_user');
    }

    public function load(ObjectManager $manager)
    {
        // We will truncate our tables by hand to return to id = 1
        $this->truncate();
        // Faker instance
        $faker = Faker\Factory::create('fr_FR');
        
        // Supply of our Provider to Faker
        $faker->addProvider(new ApoWeddingProvider());

        // Users
        $user = new User();
        $user->setEmail('user@user.com');
        $encodedPassword = $this->passwordEncoder->encodePassword($user, 'user');
        $user->setPassword($encodedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setFirstname('Alexia');
        $user->setLastname('Fontraille');
        $user->setPhoneNumber('0769868953');
        $user->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail('user2@user2.com');
        $encodedPassword = $this->passwordEncoder->encodePassword($user2, 'user2');
        $user2->setPassword($encodedPassword);
        $user2->setRoles(['ROLE_USER']);
        $user2->setFirstname('Yael');
        $user2->setLastname('Hue');
        $user2->setPhoneNumber('0745454445');
        $user2->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($user2);

        $user3 = new User();
        $user3->setEmail('user3@user3.com');
        $encodedPassword = $this->passwordEncoder->encodePassword($user3, 'user3');
        $user3->setPassword($encodedPassword);
        $user3->setRoles(['ROLE_USER']);
        $user3->setFirstname('Lucy');
        $user3->setLastname('Rome');
        $user3->setPhoneNumber('0745121232');
        $user3->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($user3);

        $userList = [
            $user,
            $user2,
            $user3,
        ];

        // A table to store our guests
        $guestList = [];

        for ($i = 1; $i <= self::NB_GUEST; $i++) {
            // a guest
            $guest = new Guest();
            $guest->setLastname($faker->lastName());
            $guest->setFirstname($faker->firstName());
            $guest->setAddress($faker->address());
            $guest->setPhoneNumber($faker->phoneNumber());
            $guest->setCreatedAt(new \DateTime());
            $guest->setUser($user);
            
            // We add guest to the list
            //! Careful we push from index 0
            $guestList[] = $guest;

            $manager->persist($guest);
        }

        // A table to store our providers
        $providerList = [];

        for ($i = 1; $i <= self::NB_PROVIDER; $i++) {
            // a provider
            $provider = new Provider();
            $provider->setSocietyName($faker->company());
            $provider->setPhoneNumber($faker->phoneNumber());
            $provider->setAddress($faker->address());
            $provider->setMail($faker->email());
            $provider->setFunction($faker->jobTitle());
            $provider->setCreatedAt(new \DateTime());
            
            // We associate 1 to 3 users at random
            //! We will manage the uniqueness with shuffle()
            shuffle($providerList);

            for ($r = 0; $r < mt_rand(1, 3); $r++) {
                // We will look for the index $r in the mixed array
                // => uniqueness is guaranteed
                $randomUser = $userList[$r];
                $provider->addUser($randomUser);
            }
            
            // We add provider to the list
            //! Careful we push from index 0
            $providerList[] = $provider;

            $manager->persist($provider);
        }

        // A table to store our tasks
        $taskList = [];

        for ($i = 1; $i <= self::NB_GUEST; $i++) {
            // a task
            $task = new Task();
            $task->setName($faker->unique()->userTasks());
            $task->setStatus(mt_rand(0,1));
            $task->setCreatedAt(new \DateTime());
            $task->setUser($user);
            
            // We add task to the list
            //! Careful we push from index 0
            $taskList[] = $task;

            $manager->persist($task);
        }
        
        $manager->flush();
    }
}
