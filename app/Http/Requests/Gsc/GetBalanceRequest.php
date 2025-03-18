<?php

namespace App\Http\Requests\Gsc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GetBalanceRequest extends FormRequest
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
            'MemberName' => 'required|string',
            'OperatorCode' => 'required|string',
            'ProductID' => 'nullable|integer',
            'MessageID' => 'required|string',
            'RequestTime' => 'required|string',
            'Sign' => 'required|string',
        ];
    }

    /**
     * Validate the request signature.
     */
    public function validateSignature(): bool
    {
        $operatorCode = $this->getOperatorCode();
        $requestTime = $this->getRequestTime();
        $methodName = $this->getMethodName();
        $secretKey = config('game.api.secret_key'); // Fetch secret key from config

        $signature = md5($operatorCode . $requestTime . $methodName . $secretKey);

        return hash_equals($signature, $this->getSign());
    }

    /**
     * Get the member name from the request.
     */
    public function getMemberName(): string
    {
        return $this->input('MemberName');
    }

    /**
     * Get the operator code from the request.
     */
    public function getOperatorCode(): string
    {
        return $this->input('OperatorCode');
    }

    /**
     * Get the request time from the request.
     */
    public function getRequestTime(): string
    {
        return $this->input('RequestTime');
    }

    /**
     * Get the method name from the request URL.
     */
    public function getMethodName(): string
    {
        return Str::lower(Str::replace(' ', '', $this->route()->getActionMethod()));
    }

    /**
     * Get the signature from the request.
     */
    public function getSign(): string
    {
        return $this->input('Sign');
    }
}