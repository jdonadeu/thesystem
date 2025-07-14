<?php

namespace App\Tipster;

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
}
