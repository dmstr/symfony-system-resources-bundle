<?php
// file generated with AI assistance: Claude Code - 2026-11-23 23:02:48 UTC

declare(strict_types=1);

namespace Dmstr\SymfonySystemResources\Command;

use Dmstr\OpenApiJsonSchema\Service\InputSchemaValidator;
use Dmstr\SymfonySystemResources\Service\MessengerMessagePruner;
use Dmstr\SymfonySystemResources\State\MessengerMessagePruneProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * CLI mirror of POST /admin/messenger_messages/prune, validated against the
 * same schema.
 */
#[AsCommand(
    name: 'app:messenger-messages:prune',
    description: 'Delete the messages of one queue older than a number of days',
)]
final class PruneMessengerMessagesCommand extends Command
{
    public function __construct(
        private readonly InputSchemaValidator $validator,
        private readonly MessengerMessagePruner $pruner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('queue', null, InputOption::VALUE_REQUIRED, 'Queue to prune (queue_name), e.g. failed')
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Keep messages younger than this many days', (string) MessengerMessagePruner::DEFAULT_DAYS)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count the messages that would be deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = filter_var($input->getOption('days'), FILTER_VALIDATE_INT);
        if ($days === false) {
            $io->error('--days must be an integer');

            return Command::INVALID;
        }
        $body = ['days' => $days, 'dryRun' => (bool) $input->getOption('dry-run')];
        if ($input->getOption('queue') !== null) {
            $body['queueName'] = (string) $input->getOption('queue');
        }

        try {
            $this->validator->validate(MessengerMessagePruneProcessor::OPERATION, $body);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }

        $result = $this->pruner->prune($body['queueName'], $body['days'], $body['dryRun']);
        $io->success(sprintf(
            '%s %d message(s) of queue "%s" created before %s.',
            $result->dryRun ? 'Would delete' : 'Deleted',
            $result->deleted,
            $result->queueName,
            $result->before->format(\DateTimeInterface::ATOM)
        ));

        return Command::SUCCESS;
    }
}
