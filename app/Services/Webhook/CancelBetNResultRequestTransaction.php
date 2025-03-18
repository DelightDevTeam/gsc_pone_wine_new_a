<?php

namespace App\Services\Webhook;

use Spatie\LaravelData\Data;

class CancelBetNResultRequestTransaction extends Data
{
    public function __construct(
        public string $OperatorId,
        public string $RequestDateTime,
        public string $Signature,
        public string $PlayerId,
        public string $Currency,
        public string $TranId,
        public string $GameCode,
        public float $BetAmount,
        public float $WinAmount,
        public string $TranDateTime, // ISO 8601 DateTime format

    ) {}
}
