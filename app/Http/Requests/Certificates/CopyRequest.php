<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CopyRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
            'number' => 'required',
            'number_tube' => 'required',
        ];
    }
}
