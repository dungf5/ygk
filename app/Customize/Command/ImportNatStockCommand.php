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

use Customize\Doctrine\DBAL\Types\UTCDateTimeTzType;
use Customize\Entity\MstShippingRoute;
use Customize\Entity\NatStockList;
use Customize\Entity\StockList;
use Customize\Service\Common\MyCommonService;
use Customize\Service\CurlPost;
use Customize\Service\MailService;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/* Run Batch: php bin/console import-nat-stock-list-command */
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
    private $shipping_code = '7015001000';

    protected static $defaultName = 'import-nat-stock-list-command';
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

        $this->handleGetDataAndImport();
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

    private function handleGetDataAndImport()
    {
        log_info('Start Get Data and Insert');
        Type::overrideType('datetimetz', UTCDateTimeTzType::class);
        $mstShippingRoute = $this->entityManager->getRepository(MstShippingRoute::class)->findOneBy(['customer_code' => $this->customer_code]);

        if (empty($mstShippingRoute)) {
            log_info('End Get Data and Insert');

            return [];
        }

        $stock_location = $mstShippingRoute->getStockLocation();

        //Change by task #1814
        //$stockList = $this->entityManager->getRepository(StockList::class)->findBy(['customer_code' => $this->customer_code, 'stock_location' => $stock_location]);
        $stockList = $this->entityManager->getRepository(StockList::class)->findBy(['stock_location' => $stock_location]);

        if (empty($stockList)) {
            log_info('End Get Data and Insert');

            return [];
        }
        $rate = $this->commonService->getTaxInfo()['tax_rate'] ?? 0;
        foreach ($stockList as $item) {
            if (empty($item['product_code'])) {
                continue;
            }

            $value = $this->commonService->getDataImportNatStockList($item['product_code'], $this->customer_code, $this->shipping_code);

            if (!empty($value) && !empty($value['jan_code'])) {
                $data = [
                  'stock_num' => $item['stock_num'],
                  'product_code' => $item['product_code'],
                  'jan_code' => $value['jan_code'],
                  'quantity' => $value['quantity'],
                  'unit_price' => $value['unit_price'] + ($value['unit_price'] * $rate / 100),
                ];

                $this->handleImportData($data);
            }
        }

        log_info('End Get Data and Insert');

        return;
    }

    private function handleImportData($data)
    {
        log_info('Insert Data to nat_stock_list, jan '.$data['jan_code']);

        try {
            if (!empty($this->entityManager->getRepository(NatStockList::class)->findOneBy(['jan' => (string) $data['jan_code']]))) {
                return;
            }

            $insertData = [
                'jan' => (string) $data['jan_code'],
                'nat_stock_num' => (int) $data['stock_num'] == 0 ? '×' : ((int) $data['stock_num'] >= 31 ? '〇' : '△'),
                'order_lot' => (string) $data['quantity'],
                'unit_price' => (int) $data['unit_price'],
            ];
            $this->entityManager->getRepository(NatStockList::class)->insertData($insertData);
        } catch (\Exception $e) {
            $message = 'Insert nat_stock_list error, jan '.$data['jan_code'];
            $message .= "\n".$e->getMessage();
            log_error($message);
            $this->pushGoogleChat($message);
        }

        return;
    }
}
