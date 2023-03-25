<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

abstract class APIRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, (new JsonResponse([
            'success' => false,
            'message' => 'The provided data failed validation',
            'errors'  => $validator->errors()
        ], Response::HTTP_BAD_REQUEST)));
    }
}
