<?php

namespace App\Http\Requests\Certificates;

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
            'outer_diameter_id' => 'integer|exists:pgsql.references.outer_diameter,id',
            'length_min' => 'numeric',
            'length_max' => 'numeric',
            'per-page' => 'integer',
            "page" => 'integer',
        ];
    }
}
