<?php

namespace App\Http\Requests\Slot;

use App\Models\User;
use App\Services\Webhook\BetNResultWebhookValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class BetNResultWebhookRequest extends FormRequest
{
    private ?User $member;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'OperatorId' => 'required|string|max:20',
            'RequestDateTime' => 'required|string|max:50',
            'Signature' => 'required|string|max:50',
            'PlayerId' => 'required|string|max:50',
            'Currency' => 'required|string|max:5',
            'TranId' => 'required|string|max:30',
            'GameCode' => 'required|string|max:50',
            'BetAmount' => 'required|numeric',
            'WinAmount' => 'nullable|numeric',
            'NetWin' => 'nullable|numeric',
            'TranDateTime' => 'required|date',
            'AuthToken' => 'nullable|string|max:500',
        ];
    }

    public function check()
    {
        $validator = BetNResultWebhookValidator::make($this)->validate();

        return $validator;
    }

    public function getOperatorId()
    {
        return $this->get('OperatorId');
    }

    public function getRequestDateTime()
    {
        return $this->get('RequestDateTime');
    }

    public function getSignature()
    {
        return $this->get('Signature');
    }

    public function getCurrency()
    {
        return $this->get('Currency');
    }

    public function getTranId()
    {
        return $this->get('TranId');
    }

    public function getGameCode()
    {
        return $this->get('GameCode');
    }

    public function getBetAmount()
    {
        return $this->get('BetAmount');
    }

    public function getWinAmount()
    {
        return $this->get('WinAmount');
    }

    public function getNetWin()
    {
        return $this->get('NetWin');
    }

    public function getTranDateTime()
    {
        return $this->get('TranDateTime');
    }

    public function getAuthToken()
    {
        return $this->get('AuthToken');
    }

    public function getMember()
    {
        $playerId = $this->getMemberName();

        return User::where('user_name', $playerId)->first();
    }

    public function getMemberName()
    {
        return $this->get('PlayerId');
    }

    public function getUserId()
    {
        $player = $this->getPlayerId();

        $user = User::where('user_name', $player)->first();

        return $user->id;
    }

    public function getPlayerId()
    {
        return $this->get('PlayerId');
    }

    public function getMethodName()
    {
        return str($this->url())->explode('/')->last();
    }

    public function getTransactions()
    {
        // Check if there is a 'transactions' key in the request, which could indicate an array of transactions
        $transactions = $this->input('transactions', []);

        if (empty($transactions)) {
            // If no 'transactions' key is found, assume single transaction structure based on individual fields
            $transactions = [
                [
                    'OperatorId' => $this->getOperatorId(),
                    'RequestDateTime' => $this->getRequestDateTime(),
                    'Signature' => $this->getSignature(),
                    'PlayerId' => $this->getPlayerId(),
                    'Currency' => $this->getCurrency(),
                    'TranId' => $this->getTranId(),
                    'GameCode' => $this->getGameCode(),
                    'BetAmount' => $this->getBetAmount(),
                    'WinAmount' => $this->getWinAmount(),
                    'NetWin' => $this->getNetWin(),
                    'TranDateTime' => $this->getTranDateTime(),
                    'AuthToken' => $this->getAuthToken(),
                ],
            ];
        } elseif (isset($transactions['OperatorId'])) {
            // If 'transactions' is an associative array (indicating a single transaction), wrap it in an array
            $transactions = [
                $transactions,
            ];
        }

        // Log the transactions for debugging
        // Log::info('Retrieved Transactions', [
        //     'transactions' => $transactions,
        // ]);

        return $transactions;
    }
}
