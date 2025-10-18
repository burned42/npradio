<?php

declare(strict_types=1);

namespace App\Command;

use App\Stream\AbstractRadioStream;
use DateTimeInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Traversable;

#[AsCommand(name: 'app:stream-info')]
final readonly class StreamInfoCommand
{
    /** @var array<string, AbstractRadioStream> */
    private array $radios;

    /**
     * @param Traversable<AbstractRadioStream> $radios
     */
    public function __construct(
        #[AutowireIterator(AbstractRadioStream::class, defaultIndexMethod: 'getRadioName')]
        Traversable $radios,
    ) {
        /** @var array<string, AbstractRadioStream> $radioArray */
        $radioArray = iterator_to_array($radios);
        $this->radios = $radioArray;
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument]
        ?string $radioName,
        #[Argument]
        ?string $streamName,
    ): int {
        $radios = $this->getRadioStreamsFromInput($radioName, $streamName);

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
                $info[] = ['Track' => $artist.' - '.$track];

                if (null !== $streamInfo->show) {
                    $show = $streamInfo->show;
                    if (null !== $streamInfo->genre) {
                        $show .= ' ('.$streamInfo->genre.')';
                    }
                    $info[] = ['Show' => $show];
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
                    $info[] = ['Moderator' => $moderator];
                }
            }

            $io->definitionList(...$info);
        }

        return Command::SUCCESS;
    }

    private function getRadioClass(string $radioName): AbstractRadioStream
    {
        return $this->radios[$radioName]
            ?? throw new InvalidArgumentException('Invalid radio name given: '.$radioName);
    }

    /**
     * @return array<string, string[]>
     */
    private function getRadioStreamsFromInput(?string $radioName, ?string $streamName): array
    {
        if (!is_string($radioName)) {
            return array_map(
                static fn (AbstractRadioStream $radio): array => $radio->getAvailableStreams(),
                $this->radios
            );
        }

        $streams = $this->getRadioClass($radioName)->getAvailableStreams();

        if (!is_string($streamName)) {
            return [$radioName => $streams];
        }

        if (!in_array($streamName, $streams, true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        return [$radioName => [$streamName]];
    }
}
