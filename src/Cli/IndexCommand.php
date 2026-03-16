<?php

declare(strict_types=1);

namespace ScipLaravel\Cli;

use RuntimeException;
use ScipLaravel\Config\IndexConfigurationResolver;
use ScipLaravel\Core\ProjectIndexer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use function ini_set;
use function sprintf;

#[AsCommand(name: 'index', description: 'Generate an index for a PHP or Laravel project')]
final class IndexCommand extends Command
{
    public function __construct(
        private readonly IndexConfigurationResolver $configurationResolver = new IndexConfigurationResolver(),
        private readonly ProjectIndexer $projectIndexer = new ProjectIndexer(),
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('project-dir', null, InputOption::VALUE_REQUIRED, 'Project directory to index')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Path to write the generated index')
            ->addOption(
                'framework',
                null,
                InputOption::VALUE_REQUIRED,
                'Framework mode: auto, php, or laravel',
            )
            ->addOption(
                'php-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Target PHP version hint: auto, 8.4, or 8.5',
            )
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit to set before indexing')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Path to a JSON or PHP config file')
            ->setHelp(
                <<<'HELP'
Generate an index for a standalone PHP or Laravel project.

Examples:
  scip-laravel index --project-dir /path/to/app --output /tmp/index.json
  scip-laravel index --config ./scip-laravel.json

Config file keys:
  projectDir
  output
  framework
  phpVersion
  memoryLimit

CLI options override config file values.
HELP,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $configuration = $this->configurationResolver->resolve($input);
            $this->setMemoryLimit($configuration->memoryLimit);

            if ($output->isVerbose()) {
                $output->writeln(sprintf('Indexing project: %s', $configuration->projectDir));
                $output->writeln(sprintf('Framework mode: %s', $configuration->framework));
                $output->writeln(sprintf('PHP version hint: %s', $configuration->phpVersion));
            }

            $index = $this->projectIndexer->index($configuration->projectDir);
            $this->filesystem->dumpFile($configuration->outputPath, $index->toJson());

            $output->writeln(sprintf('Index written to %s', $configuration->outputPath));
            return Command::SUCCESS;
        } catch (RuntimeException $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return Command::FAILURE;
        }
    }

    private function setMemoryLimit(string $memoryLimit): void
    {
        if (ini_set('memory_limit', $memoryLimit) === false) {
            throw new RuntimeException("Cannot set memory limit to $memoryLimit.");
        }
    }
}
