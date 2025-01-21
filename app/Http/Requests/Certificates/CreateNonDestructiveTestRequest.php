<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CreateNonDestructiveTestRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
            'control_object_id' => 'required|integer|exists:pgsql.certificates.control_object,id',
            'control_method_id' => 'required|integer|exists:pgsql.references.control_method,id',
            'nd_control_method_id' => 'required|integer|exists:pgsql.references.control_method,id',
            'control_result_id' => 'integer|exists:pgsql.references.control_result,id',
        ];
    }
}
