<?php

namespace App\Http\Controllers;

use App\Http\Requests\Certificates\{
    CreateMeldRequest,
    CreateRequest,
    CreateRollRequest,
    CylinderUpdateStepRequest,
    CopyRequest,
    DeleteMeldRequest,
    ListRequest,
    UpdateCommonStepRequest,
    UpdateNonDestructiveTestStepRequest,
    UpdateDetailTubeStepRequest,
    UpdateRollsSortStepRequest,
    UpdateSignatureStepRequest,
    CreateNoteRequest,
    CreateSignatureRequest,
    WallThicknessInfoRequest,
    CreateNonDestructiveTestRequest,
    DeleteSignatureRequest,
    DeleteNoteRequest,
    DeleteRollRequest,
};
use App\Models\Certificates\{
    Certificate,
    Meld,
    Roll,
    Status,
    Note,
    NonDestructiveTest,
    Signature
};
use App\Models\References\{HardnessLimit, MassFraction,};
use App\Services\Generate\CertificateGenerateService;
use App\Services\WallThicknessInfoService;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class CertificatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Список черновиков
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function listDraft(ListRequest $request): JsonResponse
    {
        return $this->getList($request, [Status::STATUS_DRAFT, Status::STATUS_REFUNDED]);
    }

    /**
     * Список опубликованных
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function listPublished(ListRequest $request): JsonResponse
    {
        return $this->getList($request, [Status::STATUS_PUBLISHED]);
    }

    /**
     * Список удаленных
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function listDeleted(ListRequest $request): JsonResponse
    {
        return $this->getList($request, [Status::STATUS_DELETED]);
    }

    /**
     * Список отправленных на согласование
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function listApprove(ListRequest $request): JsonResponse
    {
        return $this->getList($request, [Status::STATUS_APPROVE]);
    }

    /**
     * Создание сертификата
     *
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $certificate = Certificate::create(array_merge($request->all(), ['status_id' => Status::STATUS_DRAFT, 'created_at' => $request->created_at]));

        return response()->json(['success' => true, 'id' => $certificate->id], Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param UpdateCommonStepRequest $request
     * @return JsonResponse
     */
    public function updateCommonStep(int $id, UpdateCommonStepRequest $request): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificate->saveCommon($request);

        return response()->json(['success' => true]);
    }

    /**
     * Удаление/архивирование
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificate->remove();

        return response()->json(['success' => true]);
    }

    /**
     * Восстановление в черновики
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificate->restore();

        return response()->json(['success' => true]);
    }

    /**
     * Возврат на доработку
     *
     * @param int $id
     * @return JsonResponse
     */
    public function refund(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificate->refund();

        return response()->json(['success' => true]);
    }

    /**
     * Скачивания файла сертификата
     *
     * @param int $id
     * @return void
     */
    public function download(int $id)
    {
        $certificate = Certificate::find($id);

        if (!$certificate) abort(404);

        $fileName = sprintf(
            "certificate_%s_gnkt_%s.xls",
            $certificate->number,
            $certificate->number_tube
        );

        $service = new CertificateGenerateService($certificate);
        $xls = $service->generateCertificate();
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $xls->save("php://output");
    }

    /**
     * Все поля сертификата
     *
     * @param int $id
     * @return JsonResponse
     */
    public function allFields(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        return response()->json(
            [
                'commonStep' => $certificate->getCertificateArray(),
                'nonDestructiveTestsStep' => $certificate->getNonDestructiveTestsAsArray(),
                'detailTubeStep' => $certificate->getDetailTube(),
                'cylinderStep' => $certificate->cylinder,
                'notesStep' => $certificate->getNotesAsArray(),
                'signaturesStep' => $certificate->getSignaturesAsArray(),
                'rolls' => $certificate->getRolls(),
                'pressureTest' => $certificate->getPressureTest()
                    ? number_format($certificate->getPressureTest(), 2)
                    : '',
                'theoreticalMass' => $certificate->getTheoreticalMass()
                    ? number_format(
                        round($certificate->getTheoreticalMass() / 1000, 3)
                    )
                    : ''],
        );
    }

    /**
     * Сохраненные поля первого шага
     *
     * @param int $id
     * @return JsonResponse
     */
    public function nonDestructiveTestStep(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        return response()->json($certificate->getNonDestructiveTestsAsArray());
    }

    /**
     * Сохранение неразрушающего контроля
     *
     * @param UpdateNonDestructiveTestStepRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateNonDestructiveTestStep(UpdateNonDestructiveTestStepRequest $request, int $id): JsonResponse
    {
        try {
            $certificate = Certificate::find($id);

            if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

            $data = json_decode($request->body, true);

            $certificate->saveNonDestructiveTest($data);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Добавление плавки
     *
     * @param CreateMeldRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function createMeld(CreateMeldRequest $request, int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $meld = Meld::where('certificate_id', $certificate->id)->first();

        if ($meld) return response()->json(['error' => 'Плавка с таким certificate_id уже есть в базе данных!'], Response::HTTP_BAD_REQUEST);

        $meldId = Meld::create(array_merge($request->all(), ['certificate_id' => $certificate->id]))->id;
        $massFraction = MassFraction::findByCertificate($certificate);

        $data = [
            'id' => $meldId,
            'chemical_c_max' => $massFraction?->carbon,
            'chemical_mn_max' => $massFraction?->manganese,
            'chemical_si_max' => $massFraction?->silicon,
            'chemical_s_max' => $massFraction?->sulfur,
            'chemical_p_max' => $massFraction?->phosphorus,
            'dirty_type_a_max' => Certificate::DIRTY_MAX,
            'dirty_type_b_max' => Certificate::DIRTY_MAX,
            'dirty_type_c_max' => Certificate::DIRTY_MAX,
            'dirty_type_d_max' => Certificate::DIRTY_MAX,
            'dirty_type_ds_max' => Certificate::DIRTY_MAX,
        ];

        return response()->json($data);
    }

    /**
     * Удаление плавки
     *
     * @param DeleteMeldRequest $request
     * @return JsonResponse
     */
    public function deleteMeld(DeleteMeldRequest $request): JsonResponse
    {
        $meld = Meld::where('id', $request->meld_id)->where('certificate_id', $request->certificate_id)->first();
        $meld->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Добавление рулона
     *
     * @param CreateRollRequest $request
     * @return JsonResponse
     */
    public function createRoll(CreateRollRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        $meld = Meld::where('id', $request->meld_id)->where('certificate_id', $request->certificate_id)->first();
        $roll = Roll::create(array_merge($request->all(), ['meld_id' => $meld->id]));
        $hardnessLimit = HardnessLimit::findByCertificate($certificate);

        $data = [
            'id' => $roll->id,
            'serial_number' => $roll->serial_number,
            'grain_size_max' => Certificate::GRAIN_MAX,
            'hardness_om_max' => $hardnessLimit?->value,
            'hardness_ssh_max' => $hardnessLimit?->value,
            'hardness_ztv_max' => $hardnessLimit?->value,
        ];

        return response()->json($data);
    }

    /**
     * Удаление рулона
     *
     * @param DeleteRollRequest $request
     * @return JsonResponse
     */
    public function deleteRoll(DeleteRollRequest $request): JsonResponse
    {
        $roll = Roll::where('roll_id', $request->roll_id)->where('meld_id', $request->meld_id)->first();
        $roll->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Сохранение детальной информации о трубе
     *
     * @param UpdateDetailTubeStepRequest $request
     * @return JsonResponse
     */
    public function updateDetailTubeStep(UpdateDetailTubeStepRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);
        $data = json_decode($request->body, true);
        $certificate->saveDetailTube($data);

        return response()->json(['success' => true]);
    }

    /**
     * Детальная информация о трубе
     *
     * @param int $id
     * @return JsonResponse
     */
    public function detailTubeStep(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        return response()->json($certificate->getDetailTube());
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function rollsSortStep(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $result = [];

        foreach ($certificate->rolls as $roll) {
            $result[] = ['id' => $roll->id, 'serial_number' => $roll->serial_number, 'number' => $roll->number];
        }

        return response()->json($result);
    }

    /**
     * Обновление сортировки рулонов
     *
     * @param updateRollsSortStepRequest $request
     * @return JsonResponse
     */
    public function updateRollsSortStep(UpdateRollsSortStepRequest $request): JsonResponse
    {
        try {
            $certificate = Certificate::find($request->certificate_id);

            if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

            $data = json_decode($_POST['body'], true);

            Roll::updateSort($certificate, $data);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Информация о барабане
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cylinderStep(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        return response()->json($certificate->cylinder);
    }

    /**
     * Сохранение информации о барабане
     *
     * @param CylinderUpdateStepRequest $request
     * @return JsonResponse
     */
    public function cylinderUpdateStep(CylinderUpdateStepRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificate->saveCylinder($request->all());

        return response()->json(['success' => true]);
    }

    /**
     * Добавление примечания
     *
     * @param CreateNoteRequest $request
     * @return JsonResponse
     */
    public function createNote(CreateNoteRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $note = Note::create($request->all());

        return response()->json(['success' => true, 'id' => $note->id]);
    }

    /**
     * Удаление примечания
     *
     * @param DeleteNoteRequest $request
     * @return JsonResponse
     */
    public function deleteNote(DeleteNoteRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $note = Note::where('id', $request->node_id)
            ->where('certificate_id', $certificate->certificate_id)
            ->first();

        if (!$note) {
            return response()->json(['error' => 'Примечание не нейдено!'], Response::HTTP_NOT_FOUND);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Примечания
     *
     * @param int $id
     * @return JsonResponse
     */
    public function noteStep(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        return response()->json($certificate->getNotesAsArray());
    }

    /**
     * Добавление подписи
     *
     * @param CreateSignatureRequest $request
     * @return JsonResponse
     */
    public function createSignature(CreateSignatureRequest $request): JsonResponse
    {
        $data = json_decode($request->body, true);

        foreach ($data ?? [] as $row) {
            Signature::create([
                'certificate_id' => $row['certificate_id'],
                'name' => $row['name'],
                'position' => $row['position'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Удаление подписи
     *
     * @param DeleteSignatureRequest $request
     * @return JsonResponse
     */
    public function deleteSignature(DeleteSignatureRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $signature = Signature::where('id', $request->signature_id)->where('certificate_id', $certificate->id)->first();

        if (!$signature) {
            return response()->json(['error' => 'Подпись не найдена!'], Response::HTTP_NOT_FOUND);
        }

        $signature->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Подписи
     *
     * @param int $id
     * @return JsonResponse
     */
    public function signatureStep(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        return response()->json($certificate->getSignaturesAsArray());
    }

    /**
     * Обновление подписей
     *
     * @param UpdateSignatureStepRequest $request
     * @return JsonResponse
     */
    public function updateSignatureStep(UpdateSignatureStepRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $data = json_decode($request->body, true);
        $certificate->saveSignatures($data);

        return response()->json(['success' => true]);
    }

    /**
     * Параметры толщины стенки
     *
     * @param WallThicknessInfoRequest $request
     * @return JsonResponse
     */
    public function wallThicknessInfo(WallThicknessInfoRequest $request): JsonResponse
    {
        $certificate = Certificate::find($request->certificate_id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $wallThicknessInfo = new WallThicknessInfoService($certificate, $request->wallthickness_id);

        return response()->json($wallThicknessInfo->fields());
    }

    /**
     * Копирование сертификата
     *
     * @param CopyRequest $request
     * @return JsonResponse
     */
    public function copy(CopyRequest $request): JsonResponse
    {
        $checkNumber = Certificate::where('number', $request->number)->first();

        if ($checkNumber) return response()->json(['error' => 'Сертификат с таким number уже есть в базе данных!'], Response::HTTP_BAD_REQUEST);

        $check_number_tube = Certificate::where('number_tube', $request->number_tube)->first();

        if ($check_number_tube) return response()->json(['error' => 'Сертификат с таким number_tube уже есть в базе данных!'], Response::HTTP_BAD_REQUEST);

        $oldCertificate = Certificate::find($request->certificate_id);

        if (!$oldCertificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificateService = new CertificateService();
        $certificate = $certificateService->copy($oldCertificate, $request->all());

        return response()->json([
            'id' => $certificate->id,
            'success' => true
        ]);
    }

    /**
     * Отправка на согласование/Ввод в эксплуатацию
     *
     * @param int $id
     * @return JsonResponse
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $certificate = Certificate::find($id);

            if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

            if (!$certificate->standard) return response()->json(['error' => 'Нет стандарта с таким standard_id!'], Response::HTTP_NOT_FOUND);
            if (!$certificate->outerDiameter) return response()->json(['error' => 'Нет внешний диаметр с таким outer_diameter_id!'], Response::HTTP_NOT_FOUND);
            if (!$certificate->hardness) return response()->json(['error' => 'Нет группа прочности с таким hardness_id!'], Response::HTTP_NOT_FOUND);

            $certificateService = new CertificateService();
            $certificateService->approve($certificate, Status::STATUS_APPROVE);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Опубликовать сертификат
     *
     * @param int $id
     * @return JsonResponse
     */
    public function publish(int $id): JsonResponse
    {
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $certificate->status_id = Status::STATUS_PUBLISHED;
        $certificate->save();

        return response()->json(['success' => true]);
    }

    /**
     * Создание неразрушающий контроля
     *
     * @param CreateNonDestructiveTestRequest $request
     * @return JsonResponse
     */
    public function createNonDestructiveTest(CreateNonDestructiveTestRequest $request): JsonResponse
    {
        NonDestructiveTest::create($request->all());

        return response()->json(['success' => true]);
    }

    /**
     * @param Request $request
     * @param array $status
     * @return JsonResponse
     */
    private function getList(Request $request, array $status): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = Certificate::search($status);

        $count = $q->count();
        $res = $q->limit($limit)->offset($page)->get();

        $items = Certificate::map($res);

        return response()->json($items);
    }
}
