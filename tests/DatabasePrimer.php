<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DatabasePrimer
{
    public static function prime(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): void {
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $admin = new User();
        $admin->setEmail('admin@example.com')
            ->setFirstname('Admin')
            ->setLastname('User')
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setPassword($passwordHasher->hashPassword($admin, 'Admin123!'));

        $em->persist($admin);

        $category = new Category();
        $category->setName('Test Category')
            ->setDescription('Test Description')
            ->setColor('#4F46E5');

        $em->persist($category);

        $event = new Event();
        $event->setTitle('Test Event')
            ->setDescription('Test Event Description')
            ->setDate(new \DateTime('+1 month'))
            ->setEndDate(new \DateTime('+1 month +2 hours'))
            ->setLocation('Test Location')
            ->setCapacity(100)
            ->setRegisteredCount(0)
            ->setCategory($category)
            ->setStatus('published')
            ->setPrice(99.99)
            ->setOrganizer($admin)
            ->setFeatures(['Feature 1', 'Feature 2']);

        $em->persist($event);

        $em->flush();
    }
}
