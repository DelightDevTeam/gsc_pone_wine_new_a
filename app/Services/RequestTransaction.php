<?php

namespace App\Services;

use Spatie\LaravelData\Data;

class RequestTransaction extends Data
{
    public function __construct(
        public int $Status,
        public ?string $ProductID,
        public string $GameCode,
        public string $GameType,
        public int $BetId,
        public ?string $TransactionID,
        public ?string $WagerID,
        public ?float $BetAmount,
        public ?float $TransactionAmount,
        public ?float $PayoutAmount = null,
        public ?float $ValidBetAmount = null,
    ) {}
}
