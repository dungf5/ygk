<?php

declare(strict_types=1);
namespace Customize\Command;

use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
use Customize\Service\FTPService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessImportCsvOrderCommand extends Command
{
    use PluginCommandTrait;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $commonService;
    private $csvService;
    private $ftpService;

    protected static $defaultName = 'process-import-csv-order';
    protected static $defaultDescription = 'Add a short description for your command';

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager    = $entityManager;
        $this->commonService    = new MyCommonService($entityManager);
        $this->csvService       = new CSVService($entityManager);
        $this->ftpService       = new FTPService($entityManager);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
//        $arg1 = $input->getArgument('arg1');
//
//        if ($arg1) {
//            $io->note(sprintf('You passed an argument: %s', $arg1));
//        }
//
//        if ($input->getOption('option1')) {
//            // ...
//        }


        /* Connet FTP to get file*/
        $this->ftpService->getData();

        /* Read file CSV*/
        //$path           = "./html/dowload/csv/order/2023/03/20230313150102HACHU.CSV";
        $path           = "";
        $result         = $this->csvService->readFile($path);
        if ($result['status'] == 1) {
            $csvData    = $result['message'];

            if (!empty($csvData)) {
                // Read and save data
            }

            else {
                // Log empty
            }
        }

        else {
            //Log error
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
