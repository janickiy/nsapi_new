<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CreateMeldRequest  extends BaseRequest
{
    //  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'sekv' => 'numeric',
            'chemical_c' => 'numeric',
            'chemical_mn' => 'numeric',
            'chemical_si' => 'numeric',
            'chemical_s' => 'numeric',
            'chemical_p' => 'numeric',
            'dirty_type_a' => 'numeric',
            'dirty_type_b' => 'numeric',
            'dirty_type_c' => 'numeric',
            'dirty_type_d' => 'numeric',
            'dirty_type_ds' => 'numeric',
        ];
    }
}
