<?php
declare(strict_types=1);
namespace Customize\Command;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Command\PluginCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class ResetDataCommand extends Command
{
    use PluginCommandTrait;

    protected static $defaultName = 'customize:reset-data';

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Reset transaction data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        echo "abc";
        return 0;
    }
}
