<?php

namespace App\Tipster;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\PredictionRepository;
use App\Service\EventService;
use App\Service\FilesystemService;
use App\Service\TeamNameMapper;

class Tipster
{
    public function __construct(
        protected readonly EventRepository $eventRepository,
        protected readonly PredictionRepository $predictionRepository,
        protected readonly FilesystemService $filesystemService,
        protected readonly EventService $eventService,
        protected readonly TeamNameMapper $teamNameMapper,
    ) {
    }

    public function getEvent(
        string $tipsterName,
        string $date,
        string $homeTeam,
        string $visitorTeam,
        bool $commit,
    ): ?Event {
        $event = $this->eventRepository->findOneBy([
            'date' => $date,
            'homeTeam' => $homeTeam,
            'visitorTeam' => $visitorTeam,
        ]);

        if ($event !== null) {
            return $event;
        }

        if ($commit) {
            echo "Creating event [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";
            return $this->eventRepository->create($date, $homeTeam, $visitorTeam);
        } else {
            echo "$tipsterName: Event not found. [date='$date', homeTeam='$homeTeam', visitorTeam='$visitorTeam'] \n";

            $similarEvents = $this->eventService->findSimilarEvents($date, $homeTeam, $visitorTeam);
            $similarEventsText = "";

            foreach ($similarEvents as $similarEvent) {
                $similarEventsText .= " - " . $similarEvent->getDate() . ": "
                    . " '" . $homeTeam . "' => '" . $similarEvent->getHomeTeam()
                    . "' or '" . $visitorTeam . "' => '" . $similarEvent->getVisitorTeam() . "'"
                    . "\n";
            }

            if (count($similarEvents) > 0) {
                echo "\n" . $similarEventsText . "\n\n";
            }
        }

        return null;
    }
}
