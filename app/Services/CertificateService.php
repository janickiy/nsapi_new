<?php

namespace App\Services;

use App\Models\Certificates\{
    Certificate,
    Cylinder,
    Meld,
    Roll,
    Note,
    NonDestructiveTest,
    Status,
    Signature
};
use Yiisoft\Arrays\ArrayHelper;

class CertificateService
{
    /**
     * Копирование сертификата
     *
     * @param Certificate $oldCertificate
     * @param array $data
     * @return Certificate
     */
    public function copy(Certificate $oldCertificate, array $data): Certificate
    {
        $oldData = $oldCertificate->toArray();

        foreach ($data as $key => $value) {
            $oldData[$key] = $value;
        }

        $oldData['status_id'] = Status::STATUS_DRAFT;
        $certificate = Certificate::create($oldData);

        if ($certificate) {
            // Неразрушающий контроль
            foreach ($oldCertificate->nonDestructiveTests as $oldNonDestructiveTest) {
                $nonDestructiveTest = NonDestructiveTest::where('certificate_id',$certificate->id)
                    ->where('control_object_id',$oldNonDestructiveTest->control_object_id)
                    ->where('control_method_id',$oldNonDestructiveTest->control_method_id)
                    ->first();

                if (!$nonDestructiveTest) {
                    NonDestructiveTest::create([
                        'certificate_id' => $certificate->id,
                        'control_object_id' => $oldNonDestructiveTest->control_object_id,
                        'control_method_id' => $oldNonDestructiveTest->control_method_id,
                        'nd_control_method_id' => $oldNonDestructiveTest->nd_control_method_id,
                        'control_result_id' => $oldNonDestructiveTest->control_result_id,
                    ]);
                }
            }

            // Плавки
            foreach ($oldCertificate->melds as $oldMeld) {
                $meld = Meld::create(array_merge($oldMeld->toArray(), ['certificate_id' => $certificate->id]));

                // Рулоны
                foreach ($oldMeld->rolls as $oldRoll) {
                    Roll::create(array_merge($oldRoll->toArray(), ['certificate_id' => $certificate->id, 'meld_id' => $meld->id]));
                }
            }

            // Информация о барабане

            if ($oldCertificate->cylinder) {
                Cylinder::create([
                    'id' => $certificate->id,
                    'material' => $oldCertificate->cylinder->material,
                    'weight' => $oldCertificate->cylinder->weight,
                    'diameter_core' => $oldCertificate->cylinder->diameter_core,
                    'diameter_cheek' => $oldCertificate->cylinder->diameter_cheek,
                    'mark_nitrogen' => $oldCertificate->cylinder->mark_nitrogen,
                ]);
            }

            // Примечания
            foreach ($oldCertificate->notes as $oldNote) {
                Note::create([
                    'certificate_id' => $certificate->id,
                    'text' => $oldNote->text,
                ]);
            }

            // Подписи
            foreach ($oldCertificate->signatures as $oldSignature) {
                Signature::create([
                    'certificate_id' => $certificate->id,
                    'name' => $oldSignature->name,
                    'position' => $oldSignature->position,
                ]);
            }
        }

        return $certificate;
    }

    /**
     * @param Certificate $certificate
     * @param string $status
     * @return void
     * @throws \Exception
     */
    public function approve(Certificate $certificate, string $status): void
    {
        $rules = [
            'number' => 'required|string',
            'number_tube' => 'required|string',
            'created_at' => 'required',
            'product_type' => 'required',
            'standard_id' => 'required|integer',
            'hardness_id' => 'required|integer',
            'outer_diameter_id' => 'required|integer',
            'agreement_delivery' => 'required',
            'type_heat_treatment' => 'required',
            'type_quick_connection' => 'required',
            'result_hydrostatic_test' => 'required',
            'customer' => 'required',
        ];

        StringHelper::validate($certificate->toArray(), $rules);

        if (!$certificate->standard) throw new \Exception('Нет стандарта с таким standard_id!', 404);
        if (!$certificate->outerDiameter) throw new Exception('Нет внешний диаметр с таким outer_diameter_id!', 404);
        if (!$certificate->hardness) throw new Exception('Нет группа прочности с таким hardness_id!', 404);

        $tests = $certificate->getNonDestructiveTestsAsArray();

        // NonDestructive Tests Step
        foreach ($tests as $controlObjects) {
            foreach ($controlObjects as $item) {
                $rules = [
                    'control_method_id' => 'required|integer',
                    'nd_control_method_id' => 'required|integer',
                    'control_result_id' => 'required|integer',
                ];

                StringHelper::validate($item, $rules);

                $ndControlMethod = NdControlMethod::find($item['nd_control_method_id']);

                if (!$ndControlMethod) throw new Exception('Не найден НД на метод контроля с таким control_method_id', 404);

                $controlMethod = ControlMethod::find($item['control_method_id']);

                if (!$controlMethod) throw new Exception('Не найден метод контроля с таким control_method_id', 404);

                $controlResult = ControlResult::find($item['control_result_id']);

                if (!$controlResult) throw new Exception('Не найден метод контроля с таким control_result_id', 404);
            }
        }

        if (!ArrayHelper::keyExists($tests, 'cross_seams_roll_ends') && !ArrayHelper::keyExists($tests, 'longitudinal_seams') && !ArrayHelper::keyExists($tests, 'base_metal'))
            throw new Exception ('Необходимо заполнить. (longitudinal_seams,base_metal,circular_corner_seam,cross_seams_roll_ends)', 400);

        if (!$certificate->melds) {
            throw new Exception  ('Необходимо заполнить хотя бы одну плавку.');
        }

        foreach ($certificate->melds as $meld) {
            $rules = [
                'sekv' => 'numeric',
                'chemical_c' => 'numeric',
                'chemical_mn' => 'numeric',
                'chemical_si' => 'numeric',
                'chemical_s' => 'numeric',
                'chemical_p' => 'numeric',
                'dirty_type_a' => 'numeric',
                'dirty_type_b' => 'numeric',
                'dirty_type_c' => 'numeric',
                'dirty_type_d' => 'numeric',
                'dirty_type_ds' => 'numeric',
            ];

            StringHelper::validate(StringHelper::ObjectToArray($meld), $rules);

            if (!$meld->rolls) {
                throw new Exception ('Необходимо заполнить хотя бы один рулон');
            }

            foreach ($meld->rolls as $roll) {
                $rules = [
                    'meld_id' => 'integer',
                    'wall_thickness_id' => 'integer',
                    'length' => 'numeric',
                    'grain_size' => 'numeric',
                    'hardness_om' => 'numeric',
                    'hardness_ssh' => 'numeric',
                    'hardness_ztv' => 'numeric',
                    'absorbed_energy_1' => 'numeric',
                    'absorbed_energy_2' => 'numeric',
                    'absorbed_energy_3' => 'numeric',
                    'fluidity' => 'numeric',
                ];
                StringHelper::validate(StringHelper::ObjectToArray($roll), $rules);
            }
        }

        // Cylinder Step
        if ($certificate->cylinder) {
            $rules = [
                'weight' => 'numeric',
                'diameter_core' => 'numeric',
                'diameter_cheek' => 'numeric',
                'width' => 'numeric',
            ];

            StringHelper::validate($certificate->cylinder->toArray(), $rules);
        } else {
            throw new Exception ('Необходимо заполнить информацию о барабане.');
        }

        // Notes Step
        foreach ($certificate->notes->toArray() as $note) {
            $rules = [
                'text' => 'required|string',
            ];

            StringHelper::validate($note, $rules);
        }

        if ($certificate->signatures->count() < 2) throw new Exception ('Необходимо заполнить минимум 2 подписи.');

        foreach ($certificate->signatures->toArray() as $signature) {
            $rules = [
                'name' => 'required',
                'position' => 'required',
            ];

            StringHelper::validate($signature, $rules);
        }

        $certificate->status_id = $status;
        $certificate->save();
    }
}
