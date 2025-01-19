<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;

class CreateRequest extends BaseRequest
{
    //  protected function authorize()
    //  {
    //    return true;
    //   }

    protected function rules(): array
    {
        return [
            "number" => "required|string",
            "number_tube" => "required|string|unique:pgsql.certificates.certificate,number_tube",
            "rfid" => "string|string|unique:pgsql.certificates.certificate,rfid",
            "standard_id" => 'required|integer|exists:pgsql.references.standard,id',
            "hardness_id" => 'required|integer|exists:pgsql.references.hardness,id',
            "outer_diameter_id" => 'required|integer|exists:pgsql.references.outer_diameter,id',
            "gnkt_wall_depth" => 'numeric',
            'created_at' => "required",
        ];
    }
}
