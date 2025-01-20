<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CreateNoteRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
            'text' => 'required|string',
        ];
    }
}
