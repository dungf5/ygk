<?php

declare(strict_types=1);
namespace Customize\Command;

use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CSVService;
use Customize\Service\FTPService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/* Run Batch: php bin/console import-csv-order-command */
class ImportCsvOrderCommand extends Command
{
    use PluginCommandTrait;

    /** @var EntityManagerInterface */
    private $entityManager;
    private $commonService;
    private $csvService;
    private $ftpService;

    protected static $defaultName = 'import-csv-order-command';
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

        /* Read file CSV*/
        $path           = getenv("LOCAL_FTP_DIRECTORY") ?? "/html/dowload/csv/order/";
        if (getenv("APP_IS_LOCAL") == 1)
            $path       = "." . $path;

        $path           .= Date("Y/m");

        $file_list      = scandir($path);

        if (!empty($file_list) && is_array($file_list) && count($file_list)) {
            foreach ($file_list as $file)
            {
                if (!str_ends_with($file, ".csv")) continue;

                if ($this->commonService->checkFileExistInDB(['file_name' => $file, 'is_sync' => 1])) {
                    // Get data from file and save DB

                    // Update information
                    Type::overrideType('datetimetz', UTCDateTimeTzType::class);
                    $data       = [
                        "file_name"     => $file,
                        'message'       => "File sync successfully",
                        'is_sync'       => 1,
                        'up_date'       => new \DateTime(),
                    ];
                    $this->commonService->updateFileToDB($data);

                    var_dump('ok');
                    die();
                }
            }
        }

        $path           .= "/20230313150102hachu.csv";
        $result         = $this->csvService->readFile($path);
        if ($result['status'] == 1) {
            $csvData    = $result['message'];

            if (!empty($csvData)) {
                // Read and save data
                var_dump($csvData);
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
