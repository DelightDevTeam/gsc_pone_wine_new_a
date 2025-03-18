<?php

namespace App\Http\Requests\Slot;

use App\Models\User;
use App\Services\SlotWebhookValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SlotWebhookRequest extends FormRequest
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

        ];
    }

    public function check()
    {
        $validator = SlotWebhookValidator::make($this)->validate();

        return $validator;
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

    public function getMethodName()
    {
        return str($this->url())->explode('/')->last();
    }

    public function getOperatorCode()
    {
        return $this->get('OperatorId');
    }

    public function getRequestTime()
    {
        return $this->get('RequestDateTime');
    }

    public function getSign()
    {
        return $this->get('Signature');
    }

    public function test()
    {
        return 'test';
    }

    public function getTransactions()
    {
        $transactions = $this->get('Transactions', []);

        if ($transactions) {
            return $transactions;
        }

        $transaction = $this->get('Transaction', []);

        if ($transaction) {
            return [$transaction];
        }

        return [];
    }
}
