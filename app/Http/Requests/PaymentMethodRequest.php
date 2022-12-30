<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "paymentMethod" => "required",
        ];
    }

    public function messages()
    {
        return [
            'paymentMethod.required' => "ပေးချေလိုသည့် Payment အမျိုးအစားကို ရွေးချယ်ပါ။"
        ];
    }
}