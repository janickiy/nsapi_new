<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;
class UpdateNonDestructiveTestStepRequest extends BaseRequest
{
    //  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'number' => 'required|json',
        ];
    }
}
