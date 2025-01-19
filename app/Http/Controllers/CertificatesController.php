<?php

namespace App\Http\Controllers;

use App\Http\Requests\Certificates\{CreateMeldRequest,
    CreateRequest,
    CreateRollRequest,
    DeleteMeldRequest,
    ListRequest,
    UpdateCommonStepRequest,
    UpdateNonDestructiveTestStepRequest,
    DeleteRollRequest,
};
use App\Models\Certificates\{Certificate, Meld, Roll, Status,};
use App\Models\References\{HardnessLimit, MassFraction,};
use App\Services\Generate\CertificateGenerateService;
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
        $certificate = Certificate::find($id);

        if (!$certificate) return response()->json(['error' => 'Сертификат не найден!'], Response::HTTP_NOT_FOUND);

        $data = json_decode($request->body, true);

        $certificate->saveNonDestructiveTest($data);

        return response()->json(['success' => true]);
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
        $roll = Roll::where('roll_id', $request->roll_id)->where('meld_id',$request->meld_id)->first();
        $roll->delete();

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
