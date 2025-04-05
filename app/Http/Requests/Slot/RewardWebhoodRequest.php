<?php

namespace App\Http\Requests\Slot;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RewardWebhoodRequest extends FormRequest
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
            'TranId' => 'required|string|max:30',
            'RewardId' => 'required|string|max:50',
            'RewardName' => 'required|string|max:100',
            'Amount' => 'required|numeric',
            'TranDateTime' => 'required|date',
            'RewardDetail' => 'nullable|string|max:100',
        ];
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

    public function getTransactions()
    {
        $transactions = $this->input('transactions', []);

        if (empty($transactions)) {
            $transactions = [
                [
                    'OperatorId' => $this->input('OperatorId'),
                    'RequestDateTime' => $this->input('RequestDateTime'),
                    'Signature' => $this->input('Signature'),
                    'PlayerId' => $this->input('PlayerId'),
                    'Currency' => $this->input('Currency'),
                    'TranId' => $this->input('TranId'),
                    'RewardId' => $this->input('RewardId'),
                    'RewardName' => $this->input('RewardName'),
                    'Amount' => $this->input('Amount'),
                    'TranDateTime' => $this->input('TranDateTime'),
                    'RewardDetail' => $this->input('RewardDetail'),
                ],
            ];
        }

        Log::info('Retrieved Reward Transactions', [
            'transactions' => $transactions,
        ]);

        return $transactions;
    }
}
