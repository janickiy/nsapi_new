<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class DeleteSignatureRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'signature_id' => 'required|integer|exists:pgsql.certificates.signature,id',
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
        ];
    }
}
