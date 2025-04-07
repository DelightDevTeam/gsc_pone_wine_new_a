<?php

namespace App\Services\Webhook;

use App\Enums\StatusCode;
use App\Http\Requests\Slot\BetNResultWebhookRequest;
use App\Models\Webhook\BetNResult;
use Illuminate\Support\Facades\Log;

class BetNResultWebhookValidator
{
    protected float $totalTransactionAmount = 0;

    protected float $before_balance;

    protected float $after_balance;

    protected array $response;

    protected array $requestTransactions = [];

    protected function __construct(protected BetNResultWebhookRequest $request) {}

    /**
     * Main validation function
     */
    public function validate()
    {
        if (! $this->isValidSignature()) {
            return $this->response(StatusCode::InvalidSignature);
        }

        if (! $this->request->getMember()) {
            return $this->response(StatusCode::InvalidPlayer);
        }

        foreach ($this->request->getTransactions() as $transaction) {
            // Map transaction data to BetNResultRequestTransaction
            $requestTransaction = new BetNResultRequestTransaction(
                $transaction['OperatorId'],
                $transaction['RequestDateTime'],
                $transaction['Signature'],
                $transaction['PlayerId'],
                $transaction['Currency'],
                $transaction['TranId'],
                $transaction['GameCode'],
                $transaction['BetAmount'] ?? 0,
                $transaction['WinAmount'] ?? 0,
                $transaction['NetWin'] ?? 0,
                $transaction['TranDateTime'],
                $transaction['AuthToken'] ?? null
            );

            $this->requestTransactions[] = $requestTransaction;

            if ($requestTransaction->TranId && ! $this->isNewTransaction($requestTransaction)) {
                return $this->response(StatusCode::DuplicateTransaction);
            }

            $this->totalTransactionAmount += $requestTransaction->BetAmount;
        }

        if (! $this->hasEnoughBalance()) {
            return $this->response(StatusCode::InsufficientBalance);
        }

        return $this;
    }

    /**
     * Validate the request signature
     */
    protected function isValidSignature()
    {
        $method = $this->request->getMethodName();
        $tranId = $this->request->getTranId();
        $requestTime = $this->request->getRequestDateTime();
        $operatorCode = $this->request->getOperatorId();
        $secretKey = $this->getSecretKey();
        $playerId = $this->request->getPlayerId();

        // Log::info('Generating signature', [
        //     'method' => $method,
        //     'tranId' => $tranId,
        //     'requestTime' => $requestTime,
        //     'operatorCode' => $operatorCode,
        //     'secretKey' => $secretKey,
        //     'playerId' => $playerId,
        // ]);

        // Generate signature
        $signature = md5($method.$tranId.$requestTime.$operatorCode.$secretKey.$playerId);
        // Log::info('Generated signature', ['signature' => $signature]);

        return $this->request->getSignature() === $signature;
    }

    /**
     * Check if the transaction is new
     */
    protected function isNewTransaction(BetNResultRequestTransaction $transaction)
    {
        return ! $this->getExistingTransaction($transaction);
    }

    /**
     * Retrieve existing transaction based on TranId
     */
    public function getExistingTransaction(BetNResultRequestTransaction $transaction)
    {
        if (! isset($this->existingTransaction)) {
            $this->existingTransaction = BetNResult::where('tran_id', $transaction->TranId)->first();
        }

        return $this->existingTransaction;
    }

    /**
     * Calculate after balance based on transaction amount
     */
    public function getAfterBalance()
    {
        if (! isset($this->after_balance)) {
            $this->after_balance = $this->getBeforeBalance() + $this->totalTransactionAmount;
        }

        return $this->after_balance;
    }

    /**
     * Get the player's current balance before transaction
     */
    public function getBeforeBalance()
    {
        if (! isset($this->before_balance)) {
            $this->before_balance = $this->request->getMember()->wallet->balance;
        }

        return $this->before_balance;
    }

    /**
     * Check if the balance is sufficient for the transaction
     */
    protected function hasEnoughBalance()
    {
        return $this->getAfterBalance() >= 0;
    }

    /**
     * Get all request transactions
     */
    public function getRequestTransactions()
    {
        return $this->requestTransactions;
    }

    /**
     * Retrieve the secret key for signature validation
     */
    // protected function getSecretKey()
    // {
    //     return config('game.api.secret_key');
    // }

    protected function getSecretKey()
    {
        $secretKey = config('game.api.secret_key');
        // Log::info('Fetched secret key');

        return $secretKey;
    }

    /**
     * Build response with the given status code and balance details
     */
    protected function response(StatusCode $responseCode)
    {
        $this->response = [
            'Status' => $responseCode->value,
            'Description' => $responseCode->name,
            'AfterBalance' => $this->getAfterBalance(),
            'BeforeBalance' => $this->getBeforeBalance(),
        ];

        return $this;
    }

    /**
     * Retrieve the generated response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Check if validation failed
     */
    public function fails()
    {
        return isset($this->response);
    }

    /**
     * Factory method to create an instance of the validator
     */
    public static function make(BetNResultWebhookRequest $request)
    {
        return new self($request);
    }
}
