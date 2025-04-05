<?php

namespace App\Http\Requests\Slot;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class GetDaySummaryRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'OperatorId' => 'required|string|max:20',
            'RequestDateTime' => 'required|string|max:50',
            'Signature' => 'required|string|max:50',
            'Date' => 'required|date_format:Y-m-d\TH:i:s\Z', // ISO 8601 format
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

    public function getSignature()
    {
        return $this->get('Signature');
    }

    public function getRequestDate()
    {
        return $this->get('Date');
    }

    public function getCurrency()
    {
        return 'MMK';
    }

    /**
     * Get validated transaction data in a structured array.
     */
    // public function getTransactionData()
    // {
    //     // Check if there is a 'transactions' key in the request, which could indicate an array of transactions
    //     $transactions = $this->input('transactions', []);

    //     if (empty($transactions)) {
    //         // If no 'transactions' key is found, assume single transaction structure based on individual fields
    //         $transactions = [
    //             [
    //                 'OperatorId' => $this->getOperatorId(),
    //                 'RequestDateTime' => $this->getRequestDateTime(),
    //                 'Signature' => $this->getSignature(),
    //                 'Currency' => $this->getCurrency(),
    //             ],
    //         ];
    //     } elseif (isset($transactions['OperatorId'])) {
    //         // If 'transactions' is an associative array (indicating a single transaction), wrap it in an array
    //         $transactions = [
    //             $transactions,
    //         ];
    //     }

    //     // Log the transactions for debugging
    //     Log::info('Retrieved Transactions', [
    //         'transactions' => $transactions,
    //     ]);

    //     return $transactions;
    // }
    public function getTransactionData(): array
    {
        $transactionData = [
            'OperatorId' => $this->getOperatorId(),
            'RequestDateTime' => $this->getRequestDateTime(),
            'Signature' => $this->getSignature(),
            'Date' => $this->getRequestDate(),
        ];

        // Log the transaction data for debugging
        // Log::info('Retrieved Transaction Data', ['transactionData' => $transactionData]);

        return $transactionData;
    }
}
