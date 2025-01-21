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
use App\Models\References\{
    NdControlMethod,
    ControlMethod,
    ControlResult
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
                $nonDestructiveTest = NonDestructiveTest::where('certificate_id', $certificate->id)
                    ->where('control_object_id', $oldNonDestructiveTest->control_object_id)
                    ->where('control_method_id', $oldNonDestructiveTest->control_method_id)
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
        $tests = $certificate->getNonDestructiveTestsAsArray();

        // NonDestructive Tests Step
        foreach ($tests as $controlObjects) {
            foreach ($controlObjects as $item) {
                $ndControlMethod = NdControlMethod::find($item['nd_control_method_id']);

                if (!$ndControlMethod) throw new \Exception('Не найден НД на метод контроля с таким control_method_id');

                $controlMethod = ControlMethod::find($item['control_method_id']);

                if (!$controlMethod) throw new \Exception('Не найден метод контроля с таким control_method_id');

                $controlResult = ControlResult::find($item['control_result_id']);

                if (!$controlResult) throw new \Exception('Не найден метод контроля с таким control_result_id');
            }
        }

        if (!ArrayHelper::keyExists($tests, 'cross_seams_roll_ends') && !ArrayHelper::keyExists($tests, 'longitudinal_seams') && !ArrayHelper::keyExists($tests, 'base_metal'))
            throw new \Exception ('Необходимо заполнить. (longitudinal_seams,base_metal,circular_corner_seam,cross_seams_roll_ends)');

        if (!$certificate->melds) {
            throw new \Exception  ('Необходимо заполнить хотя бы одну плавку.');
        }

        foreach ($certificate->melds ?? [] as $meld) {
            if (!$meld->rolls) {
                throw new \Exception ('Необходимо заполнить хотя бы один рулон');
            }
        }

        // Cylinder Step
        if (!$certificate->cylinder) {
            throw new \Exception ('Необходимо заполнить информацию о барабане.');
        }

        if ($certificate->signatures->count() < 2) throw new \Exception ('Необходимо заполнить минимум 2 подписи.');

        $certificate->status_id = $status;
        $certificate->save();
    }
}
