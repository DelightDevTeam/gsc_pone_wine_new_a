<?php

namespace App\Http\Requests\Slot;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ResultWebhookRequest extends FormRequest
{
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
            'RoundId' => 'required|string|max:30',
            'BetIds' => 'required|array',
            'ResultId' => 'required|string|max:30',
            'GameCode' => 'required|string|max:50',
            'TotalBetAmount' => 'required|numeric',
            'WinAmount' => 'required|numeric',
            'NetWin' => 'required|numeric',
            'TranDateTime' => 'required|date',
        ];
    }

    public function getOperatorId()
    {
        return $this->get('OperatorId');
    }

    public function getRequestDateTime()
    {
        return $this->get('RequestDateTime');
    }

    public function getRoundId()
    {
        return $this->get('RoundId');
    }

    public function getBetId()
    {
        return $this->get('BetId');
    }

    public function getSignature()
    {
        return $this->get('Signature');
    }

    public function getCurrency()
    {
        return $this->get('Currency');
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
                    'OperatorId' => $this->get('OperatorId'),
                    'RequestDateTime' => $this->get('RequestDateTime'),
                    'Signature' => $this->get('Signature'),
                    'PlayerId' => $this->get('PlayerId'),
                    'Currency' => $this->get('Currency'),
                    'RoundId' => $this->get('RoundId'),
                    'BetIds' => $this->get('BetIds'),
                    'ResultId' => $this->get('ResultId'),
                    'GameCode' => $this->get('GameCode'),
                    'TotalBetAmount' => $this->get('TotalBetAmount'),
                    'WinAmount' => $this->get('WinAmount'),
                    'NetWin' => $this->get('NetWin'),
                    'TranDateTime' => $this->get('TranDateTime'),
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
