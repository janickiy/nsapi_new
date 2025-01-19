<?php

namespace App\Http\Requests\Certificates;

use Lumen\Validation\BaseRequest;
class CreateRollRequest extends BaseRequest
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
            'number' => 'required',
            'wall_thickness_id' => 'integer|exists:pgsql.references.wall_thickness.id',
            'length' => 'numeric',
            'location_seams_note' => 'string',
            'grain_size' => 'numeric',
            'hardness_note' => 'string',
            'hardness_om' => 'numeric',
            'hardness_ssh' => 'numeric',
            'hardness_ztv' => 'numeric',
            'absorbed_energy_1' => 'numeric',
            'absorbed_energy_2' => 'numeric',
            'absorbed_energy_3' => 'numeric',
            'fluidity' => 'numeric',
            'fluidity_note' => 'string',
            'relative_extension_note' => 'string',
            'test_pressure_min' => 'numeric',
            'transverse_seams_location' => 'numeric',
            'theoretical_mass' => 'numeric',
        ];
    }
}
