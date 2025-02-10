<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CompanyLoanRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'from_category_id' => 'required|integer|exists:categories,id',
            'to_category_id' => 'required|integer|exists:categories,id',
            'amount' => 'required|numeric|min:0',
        ];
    }
    public function messages(): array
    {
        return [
            'form_category_id.required' => 'Entity field is required.',
            'to_category_id.required' => 'Bank or Cash field is required.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 500,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 200)
        );
    }
}
