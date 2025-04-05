<?php

namespace App\Services\Webhook;

use Spatie\LaravelData\Data;

class BetRequestTransaction extends Data
{
    public function __construct(
        public string $OperatorId,
        public string $RequestDateTime,
        public string $Signature,
        public string $PlayerId,
        public string $Currency,
        public string $RoundId,      // Unique round id of the game
        public string $BetId,        // Unique bet transaction id
        public string $GameCode,
        public float $BetAmount,
        public string $TranDateTime, // ISO 8601 DateTime format
        public ?string $AuthToken    // Optional field, nullable
    ) {}
}
