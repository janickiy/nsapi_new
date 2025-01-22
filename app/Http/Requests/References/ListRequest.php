<?php

namespace App\Http\Requests\References;

use Lumen\Validation\BaseRequest;

class ListRequest extends BaseRequest
{
    //  protected function authorize()
    //  {
    //    return true;
    //   }

    protected function rules(): array
    {
        return [

            'per-page' => 'integer',
            "page" => 'integer',
        ];
    }
}
