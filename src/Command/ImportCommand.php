<?php

namespace App\Command;

use App\Repository\DoctorRepository;
use App\Service\Import\DataImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import',
    description: 'Import file containing patients and care requests data',
)]
class ImportCommand extends Command
{
    public function __construct(
        private DoctorRepository $doctorRepository,
        private DataImporter $dataImporter,
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('doctorId', InputArgument::REQUIRED, 'Id of doctor used to import patients')
            ->addArgument('filename', InputArgument::REQUIRED, 'File containing data to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $doctorId = $input->getArgument('doctorId');
        $filename = $input->getArgument('filename');

        if ($filename) {
            $io->note(sprintf('Processing file : %s', $filename));
        }

        $doctor = $this->doctorRepository->find($doctorId);
        if (!$doctor) {
            $io->error(sprintf('Unknown doctor Id %d', $doctorId));
            return Command::FAILURE;
        }
        $io->note(sprintf('Using identity of doctor : %s', $doctor->getFirstname() . ' ' . $doctor->getLastname()));

        $results = $this->dataImporter->importFromFile($doctor, $filename);

        foreach ($results['errors'] as $line => $violations) {
            foreach ($violations as $violation) {
                $io->warning(sprintf(
                    "Line %d %s : %s",
                    $line,
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                ));
            }
        }

        $io->success(sprintf('%d patient(s) created', count($results['patients'])));

        return Command::SUCCESS;
    }
}
