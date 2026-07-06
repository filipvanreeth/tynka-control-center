<?php

declare(strict_types= 1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\Repositories\CheckInRepository;

final class GetCheckInHandler
{
    public function __construct(
        private CheckInRepository $checkInRepository,
    ) {
    
    }
    
    public function handle(GetCheckInQuery $query): ?CheckInData
    {   
        $checkIn = $this->checkInRepository->findByUuid($query->id);
        
        if($checkIn === null) {
            return null;
        }
        
        return new CheckInData(
            id: $checkIn->id(),
            uuid: $checkIn->uuid(),
            handler: $checkIn->handler(),
            hasPeed: $checkIn->hasPeed(),
            hasPooped: $checkIn->hasPooped(),
            hadFood: $checkIn->hadFood(),
            hadSnack: $checkIn->hadSnack(),
            createdAt: $checkIn->createdAt()->format('Y-m-d H:i:s'),
        );
    }
}