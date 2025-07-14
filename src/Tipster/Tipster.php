<?php

namespace App\Tipster;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\PredictionRepository;
use App\Service\FilesystemService;

class Tipster
{
    public function __construct(
        protected readonly EventRepository $eventRepository,
        protected readonly PredictionRepository $predictionRepository,
        protected readonly FilesystemService $filesystemService,
    ) {
    }

    public function getEvent(
        bool $commit,
        int $tipsterId,
        string $date,
        string $time,
        string $homeTeam,
        string $visitorTeam,
        ?int $homeGoals = null,
        ?int $visitorGoals = null,
        ?float $odd1 = null,
        ?float $oddX = null,
        ?float $odd2 = null,
    ): ?Event {
        $event = $this->eventRepository->findOneBy([
            'tipsterId' => $tipsterId,
            'date' => $date,
            'homeTeam' => $homeTeam,
            'visitorTeam' => $visitorTeam,
        ]);

        if ($event !== null) {
            echo "Updating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";
            if ($commit) {
                $this->eventRepository->update($event, $homeGoals, $visitorGoals, $odd1, $oddX, $odd2);
            }
            return $event;
        }

        if ($commit) {
            echo "Creating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

            return $this->eventRepository->create(
                $tipsterId,
                $date,
                $time,
                $homeTeam,
                $visitorTeam,
                $homeGoals,
                $visitorGoals,
                $odd1,
                $oddX,
                $odd2
            );
        }

        return null;
    }
}
