<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;
class UpdateNonDestructiveTestStepRequest extends BaseRequest
{
    protected function rules(): array
    {
        return [
            'number' => 'required|json',
        ];
    }
}
