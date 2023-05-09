<?php

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

namespace Eccube\Command;

use Eccube\Repository\PluginRepository;
use Eccube\Service\PluginService;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;

trait PluginCommandTrait
{
    /**
     * @var PluginService
     */
    protected $pluginService;

    /**
     * @var PluginRepository
     */
    protected $pluginRepository;

    /**
     * @param PluginService $pluginService
     * @required
     */
    public function setPluginService(PluginService $pluginService)
    {
        $this->pluginService = $pluginService;
    }

    /**
     * @param PluginRepository $pluginRepository
     * @required
     */
    public function setPluginRepository(PluginRepository $pluginRepository)
    {
        $this->pluginRepository = $pluginRepository;
    }

    protected function clearCache(SymfonyStyle $io)
    {
        $command = 'cache:clear --no-warmup';
        try {
            $io->text(sprintf('<info>Run %s</info>...', $command));
            $process = new Process('bin/console '.$command);
            $process->mustRun();
            $io->text($process->getOutput());
        } catch (ProcessFailedException $e) {
            $io->error($e->getMessage());
        }
    }

    /**
     * Cleanup any needed table abroad TRUNCATE SQL function
     *
     * @param string $className (example: App\Entity\User)
     * @param EntityManagerInterface $em
     *
     * @return bool
     */
    protected function truncateTable(string $className, EntityManagerInterface $em): bool
    {
        $cmd = $em->getClassMetadata($className);
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $connection->query('TRUNCATE TABLE '.$cmd->getTableName());
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
            $em->flush();
        } catch (\Exception $e) {
            log_error($e->getMessage());
            $connection->rollback();

            return false;
        }

        return true;
    }
}
