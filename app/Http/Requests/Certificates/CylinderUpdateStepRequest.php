<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CylinderUpdateStepRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
            'weight' => 'integer',
            'diameter_core' => 'integer',
            'diameter_cheek' => 'integer',
            'width' => 'integer',
        ];
    }
}
