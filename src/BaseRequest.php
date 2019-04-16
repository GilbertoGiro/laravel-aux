<?php

namespace LaravelAux;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Waavi\Sanitizer\Laravel\SanitizesInput;

abstract class BaseRequest extends FormRequest
{
    use SanitizesInput;

    public function validateResolved()
    { {
            $this->sanitize();
            parent::validateResolved();
        }
    }

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
        return [];
    }

    /**
     * Validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Attributes Name
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * Return errors array if Error is disparate
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
