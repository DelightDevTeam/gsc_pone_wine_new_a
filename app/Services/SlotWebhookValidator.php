<?php

namespace App\Services;

use App\Enums\StatusCode;
use App\Http\Requests\Slot\SlotWebhookRequest;
use App\Models\Admin\SeamlessTransaction;
use App\Models\Wager;
use App\Services\RequestTransaction;
use Illuminate\Support\Facades\Log;

class SlotWebhookValidator
{
    protected ?SeamlessTransaction $existingTransaction = null;

    protected ?Wager $existingWager = null;

    protected float $totalTransactionAmount = 0;

    protected float $before_balance = 0;

    protected float $after_balance = 0;

    protected array $response = [];

    /**
     * @var RequestTransaction[]
     */
    protected $requestTransactions = [];

    protected function __construct(protected SlotWebhookRequest $request)
    {
        // Log::info('SlotWebhookValidator initialized', ['request' => $request->all()]);
    }

    public function validate()
    {
        // Log::info('Starting validation');

        if (! $this->isValidSignature()) {
            // Log::warning('Invalid signature detected');

            return $this->response(StatusCode::InvalidSignature);
        }

        if (! $this->request->getMember()) {
            // Log::warning('Invalid player detected');

            return $this->response(StatusCode::InvalidPlayerPassword);
        }

        foreach ($this->request->getTransactions() as $transaction) {
            // Log::info('Processing transaction', ['transaction' => $transaction]);

            $requestTransaction = RequestTransaction::from($transaction);
            $this->requestTransactions[] = $requestTransaction;

            if (! in_array($this->request->getMethodName(), ['bet', 'buyin', 'buyout']) && $this->isNewWager($requestTransaction)) {
                // Log::warning('Invalid game ID detected', ['transaction' => $requestTransaction]);

                return $this->response(StatusCode::BetTransactionNotFound);
            }

            $this->totalTransactionAmount += $requestTransaction->TransactionAmount;
        }

        // Log::info('Validation passed');

        return $this;
    }

    protected function isValidSignature()
    {
        $method = $this->request->getMethodName();
        $requestTime = $this->request->getRequestTime();
        $operatorCode = $this->request->getOperatorCode();
        $secretKey = $this->getSecretKey();
        $playerId = $this->request->getMemberName();

        // Log the values used for signature generation
        // Log::info('Generating signature', [
        //     'method' => $method,
        //     'requestTime' => $requestTime,
        //     'operatorCode' => $operatorCode,
        //     'secretKey' => $secretKey,
        //     'playerId' => $playerId,
        // ]);

        // Generate the signature
        $signature = md5($method.$requestTime.$operatorCode.$secretKey.$playerId);

        // Log::info('Generated signature', ['signature' => $signature]);

        return $this->request->getSign() === $signature;
    }

    protected function getSecretKey()
    {
        $secretKey = config('game.api.secret_key');
        // Log::info('Fetched secret key');

        return $secretKey;
    }

    protected function response(StatusCode $responseCode)
    {
        // Log::info('Building response', ['responseCode' => $responseCode->name]);

        $this->response = SlotWebhookService::buildResponse(
            $responseCode,
            $this->request->getMember() ? $this->getAfterBalance() : 0,
            $this->request->getMember() ? $this->getBeforeBalance() : 0
        );

        // Log::info('Response built', ['response' => $this->response]);

        return $this;
    }

    public function getResponse()
    {
        // Log::info('Returning response', ['response' => $this->response]);

        return $this->response;
    }

    public function fails()
    {
        $fails = isset($this->response) && ! empty($this->response);
        // Log::info('Checking if validation fails', ['fails' => $fails]);

        return $fails;
    }

    public static function make(SlotWebhookRequest $request)
    {
        return new self($request);
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
            $this->before_balance = $this->request->getMember()->wallet->balance;
        }

        return $this->before_balance;
    }
}
