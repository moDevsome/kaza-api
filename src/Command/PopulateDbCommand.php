<?php

namespace Api\Command;

use Api\Entity\Equipment;
use Api\Entity\Host;
use Api\Entity\Location;
use Api\Entity\LocationArea;
use Api\Entity\Lodging;
use Api\Entity\Picture;
use Api\Entity\Tag;
use Api\Entity\User;
use Api\Object\Business\LodgingObject;
use Api\Object\Business\HostObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;

#[AsCommand(
    name: 'populate-db',
    description: 'Add tests data inside the database.',
)]
class PopulateDbCommand extends Command
{

    private readonly array $data; // Array of <LodgingObject>
    private array $insertedHostEntities; // Array of <Host>
    private array $insertedLocationEntities; // Array of <Location>
    private array $insertedEquipmentEntities; // Array of <Equipment>
    private array $insertedTagEntities; // Array of <Tag>

    private function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();

        // Do not change the order of items
        foreach (
            [
                'content_translation',
                'equipment',
                'picture',
                'lodging',
                'equipment_lodging',
                'tag',
                'lodging_tag',
                'host',
                'user',
                'location',
            ] as $table
        ) {
            $connection->executeQuery('delete from ' . $table);
            $connection->executeQuery('alter table ' . $table . ' auto_increment=0');
        }
    }

    private function loadDatas(): void
    {
        $jsonFilePath = implode(DIRECTORY_SEPARATOR, [$this->kernel->getProjectDir(), 'var', 'logements.json']);
        $testDatas = json_decode(file_get_contents($jsonFilePath));

        if (!$testDatas)
            throw new Exception('Failed to load the JSON file.');

        $objects = array_map(
            fn($testData) => new LodgingObject(
                $testData->id,
                $testData->title,
                $testData->cover,
                $testData->pictures,
                $testData->description,
                $testData->host = new HostObject($testData->host->name, $testData->host->picture),
                $testData->rating,
                $testData->location,
                $testData->equipments,
                $testData->tags
            ),
            $testDatas
        );

        $this->data = $objects;
    }

    private function findLodgingLocationEntity(string $lodgingLocationString): Location|null
    {

        $parsedLocation = array_map(fn($segment) => trim($segment), explode(' - ', $lodgingLocationString));
        if (count($parsedLocation) === 2) {
            return array_find($this->insertedLocationEntities, fn($locationEntity) => $locationEntity->getName() === $parsedLocation[1] and $locationEntity->getArea()->getName() === $parsedLocation[0]);
        }
        return null;
    }

    /**
     * Create each test User and the associated Host entity
     */
    private function createUsers()
    {
        $slugger = new AsciiSlugger();
        $created = array();
        foreach ($this->data as $testLodging) {

            // --- Create User
            $email = strtolower($slugger->slug($testLodging->host->name)) . '@exemple.com';

            if (in_array($email, $created))
                continue;

            $created[] = $email;

            $userEntity = new User();
            $userEntity->setEmail($email);
            $userEntity->setRoles(['REGISTERED']);
            $userEntity->setPassword($this->passwordHasher->hashPassword(
                $userEntity,
                'john_weak'
            ));

            $this->entityManager->persist($userEntity);
            $this->entityManager->flush();

            // --- Create Host
            $identity = array_filter(explode(' ', $testLodging->host->name), fn($segment) => preg_match('/[A-z]/i', $segment));
            if (count($identity) < 2)
                throw new Exception('Unable to build host identity with the string ' . $testLodging->host->name);

            $hostEntity = new Host();
            $hostEntity->setFirstname($identity[0]);
            $hostEntity->setLastname($identity[1]);
            $hostEntity->setPicture($testLodging->host->picture);
            $hostEntity->setUser($userEntity);

            $this->entityManager->persist($hostEntity);
            $this->entityManager->flush();
            $this->insertedHostEntities[] = $hostEntity;
        }
    }

    /**
     * Insert each test equipment into the database
     */
    private function createEquipments(): void
    {
        $created = array();
        foreach ($this->data as $testLodging) {

            foreach ($testLodging->equipments as $equipment) {

                if (in_array($equipment, $created))
                    continue;

                $created[] = $equipment;

                $equipmentEntity = new Equipment();
                $equipmentEntity->setName($equipment);

                $this->entityManager->persist($equipmentEntity);
                $this->entityManager->flush();
                $this->insertedEquipmentEntities[] = $equipmentEntity;
            }
        }
    }

    /**
     * Insert each test equipment into the database
     */
    private function createLocations(): void
    {
        $createdLocations = array(); // Array of <string>
        $createdLocationAreas = $this->entityManager->getRepository(LocationArea::class)->findAll();
        foreach ($this->data as $testLodging) {

            if (in_array($testLodging->location, $createdLocations))
                continue;

            $createdLocations[] = $testLodging->location;

            $parsedLocation = array_map(fn($segment) => trim($segment), explode(' - ', $testLodging->location));
            if (count($parsedLocation) === 2) {

                // --- Insert location area ---
                $locationAreaEntity = array_find($createdLocationAreas, fn($la) => $la->getName() === $parsedLocation[0]);
                if (!$locationAreaEntity) {

                    $locationAreaEntity = new LocationArea();
                    $locationAreaEntity->setName($parsedLocation[0]);

                    $this->entityManager->persist($locationAreaEntity);
                    $this->entityManager->flush();

                    $createdLocationAreas[] = $locationAreaEntity;
                }

                // --- Insert location ---
                $locationEntity = new Location();
                $locationEntity->setName($parsedLocation[1]);
                $locationEntity->setArea($locationAreaEntity);

                $this->entityManager->persist($locationEntity);
                $this->entityManager->flush();

                $this->insertedLocationEntities[] = $locationEntity;
            }
        }
    }

    /**
     * Insert each test tag into the database
     */
    private function createTags(): void
    {
        $created = array();
        foreach ($this->data as $testLodging) {

            foreach ($testLodging->tags as $tag) {

                if (in_array($tag, $created))
                    continue;

                $created[] = $tag;
                $tagEntity = new Tag();
                $tagEntity->setName($tag);

                $this->entityManager->persist($tagEntity);
                $this->entityManager->flush();
                $this->insertedTagEntities[] = $tagEntity;
            }
        }
    }

    /**
     * Insert each test lodging into the database
     */
    private function createLodgings(): void
    {
        foreach ($this->data as $testLodging) {

            $hostEntity = array_find($this->insertedHostEntities, fn($entity) => $entity->getFirstName() . ' ' . $entity->getLastName() === $testLodging->host->name);
            if ($hostEntity === null)
                throw new Exception('Unable to find host entity for the lodging ' . $testLodging->id);

            $locationEntity = $this->findLodgingLocationEntity($testLodging->location);
            if ($locationEntity === null)
                throw new Exception('Unable to find location entity for the lodging ' . $testLodging->id);

            // --- Insert lodging ---
            $lodgingEntity = new Lodging();
            $lodgingEntity->setTitle($testLodging->title);
            $lodgingEntity->setCover($testLodging->cover);
            $lodgingEntity->setDescription($testLodging->description);
            $lodgingEntity->setHost($hostEntity);
            $lodgingEntity->setRating($testLodging->rating);
            $lodgingEntity->setLocation($locationEntity);

            // --- Add pictures ---
            foreach ($testLodging->pictures as $picture) {

                $pictureEntity = new Picture();
                $pictureEntity->setPath($picture);

                $lodgingEntity->addPicture($pictureEntity);
            }

            // --- Add equipments ---
            foreach (array_filter($this->insertedEquipmentEntities, fn($entity) =>  in_array($entity->getName(), $testLodging->equipments)) as $equipmentEntity) {
                $lodgingEntity->addEquipment($equipmentEntity);
            }

            // --- Add tags ---
            foreach (array_filter($this->insertedTagEntities, fn($entity) =>  in_array($entity->getName(), $testLodging->tags)) as $tagEntity) {
                $lodgingEntity->addTag($tagEntity);
            }

            $this->entityManager->persist($lodgingEntity);
            $this->entityManager->flush();
        }
    }

    public function __invoke(
        #[Argument]
        #[Ask('This command will reset all the database, do you confirm? [yes] or [no]', 'no')]
        string $confirm,
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($confirm !== 'yes')
                throw new Exception('Command cancelled.');

            $this->loadDatas();
            $this->clearDatabase();
            $this->createUsers();
            $this->createEquipments();
            $this->createTags();
            $this->createLocations();
            $this->createLodgings();
            $io->success('The database has been well populated.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KernelInterface $kernel,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }
}
