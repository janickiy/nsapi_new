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
            "number_tube" => "required|string|unique:certificate,number_tube",
            "rfid" => "string|string|unique:certificate,rfid",
            "standard_id" => 'required|integer|exists:standard,id',
            "hardness_id" => 'required|integer|exists:hardness,id',
            "outer_diameter_id" => 'required|integer|exists:outer_diameter,id',
            "gnkt_wall_depth" => 'numeric',
            'created_at' => "required",
        ];
    }
}
