<?php

namespace App\Services\Slot;

use App\Enums\SlotWebhookResponseCode;
use App\Http\Requests\Slot\WebhookRequest;
use App\Models\SeamlessTransaction;
use App\Models\Wager;
use App\Services\Slot\Dto\RequestTransaction;
use Illuminate\Support\Facades\Log;

class SlotWebhookValidator
{
    protected ?SeamlessTransaction $existingTransaction;

    protected ?SeamlessTransaction $existingWager;

    protected float $totalTransactionAmount = 0;

    protected float $before_balance;

    protected float $after_balance;

    protected array $response;

    /**
     * @var RequestTransaction[]
     */
    protected $requestTransactions;

    protected bool $hasDuplicateTransaction = false;

    protected function __construct(protected WebhookRequest $request) {}

    public function validate()
    {
        if (! $this->isValidSignature()) {
            return $this->response(SlotWebhookResponseCode::InvalidSign);
        }

        if (! $this->request->getMember()) {
            return $this->response(SlotWebhookResponseCode::MemberNotExists);
        }

        foreach ($this->request->getTransactions() as $transaction) {
            $requestTransaction = RequestTransaction::from($transaction);

            $this->requestTransactions[] = $requestTransaction;

            // Check for duplicate transaction
            if ($requestTransaction->TransactionID && ! $this->isNewTransaction($requestTransaction)) {
                Log::info('Duplicate transaction detected during validation', [
                    'transaction_id' => $requestTransaction->TransactionID,
                ]);
                $this->hasDuplicateTransaction = true;
                return $this->response(SlotWebhookResponseCode::DuplicateTransaction);
            }

            if (! in_array($this->request->getMethodName(), ['placebet', 'bonus', 'jackpot', 'buyin', 'buyout', 'pushbet']) && $this->isNewWager($requestTransaction)) {
                return $this->response(SlotWebhookResponseCode::BetNotExist);
            }

            $this->totalTransactionAmount += $requestTransaction->TransactionAmount;
        }

        if (! $this->hasEnoughBalance()) {
            return $this->response(SlotWebhookResponseCode::MemberInsufficientBalance);
        }

        return $this;
    }

    protected function isValidSignature()
    {
        $operatorCode = $this->request->getOperatorCode();
        $requestTime = $this->request->getRequestTime();
        $method = $this->request->getMethodName();
        $secretKey = $this->getSecretKey();
        $signature = md5($operatorCode.$requestTime.$method.$secretKey);
        return $this->request->getSign() == $signature;
    }

    protected function isNewWager(RequestTransaction $transaction)
    {
        return ! $this->getExistingWager($transaction);
    }

    public function getExistingWager(RequestTransaction $transaction)
    {
        if (! isset($this->existingWager)) {
            $this->existingWager = SeamlessTransaction::where('wager_id', $transaction->WagerID)->first();
        }

        return $this->existingWager;
    }

    protected function isNewTransaction(RequestTransaction $transaction)
    {
        $existing = $this->getExistingTransaction($transaction);
        if ($existing) {
            Log::info('Existing transaction found', [
                'transaction_id' => $transaction->TransactionID,
                'existing_id' => $existing->id,
            ]);
        }
        return ! $existing;
    }

    public function getExistingTransaction(RequestTransaction $transaction)
    {
        if (! isset($this->existingTransaction)) {
            $this->existingTransaction = SeamlessTransaction::where('transaction_id', $transaction->TransactionID)
                ->first();
        }

        return $this->existingTransaction;
    }

    public function hasDuplicateTransaction(): bool
    {
        return $this->hasDuplicateTransaction;
    }

    public function getAfterBalance()
    {
        if (! isset($this->after_balance)) {
            $this->after_balance = $this->getBeforeBalance() + $this->totalTransactionAmount;
        }

        return $this->after_balance;
    }

    public function getBeforeBalance()
    {
        if (! isset($this->before_balance)) {
            $this->before_balance = $this->request->getMember()->balanceFloat;
        }

        return $this->before_balance;
    }

    protected function hasEnoughBalance()
    {
        return $this->getAfterBalance() >= 0;
    }

    public function getRequestTransactions()
    {
        return $this->requestTransactions ?? [];
    }

    protected function getSecretKey()
    {
        return config('game.api.secret_key');
    }

    protected function response(SlotWebhookResponseCode $responseCode)
    {
        $this->response = SlotWebhookService::buildResponse(
            $responseCode,
            $this->request->getMember() ? $this->getAfterBalance() : 0,
            $this->request->getMember() ? $this->getBeforeBalance() : 0
        );

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function fails()
    {
        return isset($this->response);
    }

    public static function make(WebhookRequest $request)
    {
        return new self($request);
    }
}