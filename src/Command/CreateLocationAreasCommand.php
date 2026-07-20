<?php

namespace Api\Command;


use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Api\Entity\ContentTranslation;
use Api\Entity\LocationArea;
use Api\Object\Business\CreateLocationAreaRequestObject;
use Api\Service\Business\LocationAreaObjectHandler;

#[AsCommand(
    name: 'create-location-areas',
    description: 'Create the location areas',
)]
class CreateLocationAreasCommand extends Command
{
    public function __construct(
        private readonly LocationAreaObjectHandler $handler,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        try {

            $alreadyExistLocationArea = array_map(fn($entity) => $entity->getName(), $this->entityManager->getRepository(LocationArea::class)->findAll());

            foreach (
                array_filter([
                    'Auvergne-Rhône-Alpes',
                    'Bourgogne Franche-Comté',
                    'Bretagne',
                    'Centre-Val de Loire',
                    'Corse',
                    'Grand Est',
                    'Hauts-de-France',
                    'Ile de France',
                    'Normandie',
                    'Nouvelle-Aquitaine',
                    'Occitanie',
                    'Pays de la Loire',
                    'Provence-Alpes-Côte d\'Azur',
                    'Guadeloupe',
                    'Guyane',
                    'Martinique',
                    'La Réunion',
                    'Mayotte'
                ], fn($locationAreaName) => !in_array($locationAreaName, $alreadyExistLocationArea)) as $locationArea
            ) {

                $this->handler->createOne(new CreateLocationAreaRequestObject($locationArea), false);
            }

            $io->success('The location areas has been well created.');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
