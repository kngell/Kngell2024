<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheGcCommand extends Command
{
    protected static $defaultName = 'cache:gc';
    protected static $defaultDescription = 'Run cache garbage collection';

    public function __construct(
        private CacheGarbageCollector $garbageCollector,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'expired-only',
                null,
                InputOption::VALUE_NONE,
                'Only clean expired items, not full garbage collection',
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force garbage collection even if recently run',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting cache garbage collection...');

        try {
            if ($input->getOption('expired-only')) {
                $results = $this->garbageCollector->collectExpiredOnly();
                $output->writeln(sprintf(
                    'Cleaned %d expired cache items (scanned %d keys)',
                    $results['removed'],
                    $results['scanned'],
                ));
            } else {
                $results = $this->garbageCollector->collect();
                $output->writeln(sprintf(
                    'Garbage collection completed: %d items collected',
                    $results['collected'],
                ));
            }

            if (!empty($results['errors'])) {
                foreach ($results['errors'] as $error) {
                    $output->writeln("<error>Error: $error</error>");
                }
                return Command::FAILURE;
            }

            $output->writeln('<info>Cache cleanup completed successfully</info>');
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}