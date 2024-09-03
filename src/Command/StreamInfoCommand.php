<?php

declare(strict_types=1);

namespace App\Command;

use App\Stream\AbstractRadioStream;
use DateTimeInterface;
use InvalidArgumentException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Traversable;

#[AsCommand(name: 'app:stream-info')]
final class StreamInfoCommand extends Command
{
    /** @var array<string, AbstractRadioStream> */
    private readonly array $radios;

    /**
     * @param Traversable<AbstractRadioStream> $radios
     */
    public function __construct(
        #[TaggedIterator(AbstractRadioStream::class, defaultIndexMethod: 'getRadioName')]
        Traversable $radios
        Traversable $radios,
    ) {
        $this->radios = iterator_to_array($radios);

        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('radio-name')
            ->addArgument('stream-name');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $radios = $this->getRadioStreamsFromInput($input);

        $io = new SymfonyStyle($input, $output);
        $outputStyle = new OutputFormatterStyle('#3daee9');
        $io->getFormatter()->setStyle('info', $outputStyle);
        $io->getFormatter()->setStyle('comment', $outputStyle);

        foreach ($radios as $radioName => $streams) {
            $io->section($radioName);

            $radioStream = $this->getRadioClass($radioName);
            $info = [];
            foreach ($streams as $streamName) {
                if ([] !== $info) {
                    $info[] = new TableSeparator();
                }
                $info[] = $streamName;

                $streamInfo = $radioStream->getStreamInfo($streamName);

                $artist = $streamInfo->artist ?? 'n/a';
                $track = $streamInfo->track ?? 'n/a';
                $info[]['Track'] = $artist.' - '.$track;

                if (null !== $streamInfo->show) {
                    $show = $streamInfo->show;
                    if (null !== $streamInfo->genre) {
                        $show .= ' ('.$streamInfo->genre.')';
                    }
                    $info[]['Show'] = $show;
                }

                if (null !== $streamInfo->moderator) {
                    $moderator = $streamInfo->moderator;
                    if (
                        $streamInfo->showStartTime instanceof DateTimeInterface
                        && $streamInfo->showEndTime instanceof DateTimeInterface
                    ) {
                        $moderator .= ' ('.$streamInfo->showStartTime->format('H:i')
                            .' - '.$streamInfo->showEndTime->format('H:i').')';
                    }
                    $info[]['Moderator'] = $moderator;
                }
            }

            $io->definitionList(...$info);
        }

        return Command::SUCCESS;
    }

    private function getRadioClass(string $radioName): AbstractRadioStream
    {
        if (!array_key_exists($radioName, $this->radios)) {
            throw new InvalidArgumentException('Invalid radio name given');
        }

        return $this->radios[$radioName];
    }

    /**
     * @return array<string, string[]>
     */
    private function getRadioStreamsFromInput(InputInterface $input): array
    {
        $radioName = $input->getArgument('radio-name');
        if (!is_string($radioName)) {
            return array_map(
                static fn (AbstractRadioStream $radio): array => $radio->getAvailableStreams(),
                $this->radios
            );
        }

        $streams = $this->getRadioClass($radioName)->getAvailableStreams();

        $streamName = $input->getArgument('stream-name');
        if (!is_string($streamName)) {
            return [$radioName => $streams];
        }

        if (!in_array($streamName, $streams, true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        return [$radioName => [$streamName]];
    }
}
