<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\Operation;
use App\Service\OperationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as IO;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Throwable;

#[AsCommand(name: "app:operation")]
class OperationCommand extends Command
{
    public function __construct(
        private readonly string $sourceDir,
        private readonly OperationService $operationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: Operation::OPT_ACTION,
            shortcut: Operation::SHORT_ACTION,
            mode: IO::VALUE_REQUIRED,
            description: Operation::DESC_ACTION,
            default: Operation::PLUS->value,
            suggestedValues: Operation::toArray(),
        );

        $this->addOption(
            name: Operation::OPT_FILE,
            shortcut: Operation::SHORT_FILE,
            mode: IO::VALUE_REQUIRED,
            description: Operation::DESC_FILE,
            default: Operation::DEFAULT_FILE,
        );
    }

    protected function execute(Input $input, Output $output): int
    {
        try {
            $input->validate();
            $action = Operation::from($input->getOption(Operation::OPT_ACTION));
            $file = sprintf("%s/%s", $this->sourceDir, $input->getOption(Operation::OPT_FILE));
            $this->operationService->run($action, $file);
            $result = Command::SUCCESS;
        } catch (Throwable) {
            $result = Command::FAILURE;
        }

        return $result;
    }
}
