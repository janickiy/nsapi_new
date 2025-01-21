<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class WallThicknessInfoRequest extends BaseRequest
{
//  protected function authorize()
    //  {
    //    return true;
    //   }
    protected function rules(): array
    {
        return [
            'certificate_id' => 'required|integer|exists:pgsql.certificates.certificate,id',
            'wallthickness_id' => 'required|integer|exists:pgsql.references.wall_thickness,id',
        ];
    }
}
