<?php

declare(strict_types=1);

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Command;

use Customize\Entity\NatStockList;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CurlPost;
use Customize\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/* Run Batch: php bin/console import-nat-stock-command */
class ImportNatStockCommand extends Command
{
    use PluginCommandTrait;
    use CurlPost;

    /** @var EntityManagerInterface */
    private $entityManager;
    /**
     * @var MailService
     */
    private $mailService;
    /**
     * @var MyCommonService
     */
    private $commonService;
    private $customer_code = '7015';

    protected static $defaultName = 'import-nat-stock-command';
    protected static $defaultDescription = 'Process Import Nat Stock List Data';

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->commonService = new MyCommonService($entityManager);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        log_info('---------------------------------------');
        log_info('Start Process Import Nat Stock List Data');

        $this->handleProcess();

        log_info('End Process Import Nat Stock List Data');

        return 0;
    }

    private function handleProcess()
    {
        // Truncate nat_stock_list
        $result = $this->handleDelete();
        if (!$result) {
            return;
        }

        $data = $this->handleGetData();

    }

    private function handleDelete()
    {
        log_info('Start Delete all nat_stock_list');
        $result = $this->truncateTable(NatStockList::class, $this->entityManager);
        log_info('End Delete all nat_stock_list');

        if (!$result) {
            $this->pushGoogleChat("Process Delete Nat Stock List Data.\nCan't truncate table nat_stock_list. Please check log!!!");
        }

        return $result;
    }

    private function handleGetData()
    {
        log_info('Start Get Data');
        //$stock_location = $this->entityManager->getRepository()
    }
}
