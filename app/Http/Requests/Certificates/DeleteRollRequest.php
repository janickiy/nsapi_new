<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class DeleteRollRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
            'meld_id' => 'required|integer|exists:pgsql.certificates.meld,id',
            'roll_id' => 'required|integer|exists:pgsql.certificates.roll,id',
        ];
    }
}
