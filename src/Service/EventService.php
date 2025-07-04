<?php

namespace App\Service;

use App\Repository\EventRepository;

class EventService
{
    private const int SIMILAR_THRESHOLD = 50;

    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    public function findSimilarEvents(string $date, string $homeTeam, string $visitorTeam): array
    {
        $similarEvents = [];
        $events = $this->eventRepository->findBy(['date' => $date]);

        foreach ($events as $event) {
            similar_text($homeTeam, $event->getHomeTeam(), $homePct);
            similar_text($visitorTeam, $event->getVisitorTeam(), $visitorPct);

            if ($homePct > self::SIMILAR_THRESHOLD || $visitorPct > self::SIMILAR_THRESHOLD) {
                $similarEvents[] = $event;
            }
        }

        return $similarEvents;
    }
}
