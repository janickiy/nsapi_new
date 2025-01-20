<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CreateSignatureRequest extends BaseRequest
{
// protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'body' => 'required|json',
        ];
    }
}
