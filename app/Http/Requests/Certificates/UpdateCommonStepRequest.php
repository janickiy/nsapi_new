<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class UpdateCommonStepRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }

    protected function rules(): array
    {
        return [
            'number' => 'required',
            'number_tube' => 'required',
            'standard_id' => 'integer|exists:pgsql.references.standard,id',
            'hardness_id' => 'integer|exists:pgsql.references.hardness,id',
            'outer_diameter_id' => 'integer|exists:pgsql.references.outer_diameter,id',
            'length_geophysical_cable' => 'integer',
        ];
    }
}
