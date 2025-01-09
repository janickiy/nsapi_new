<?php

namespace App\Http\Controllers;

use App\Models\Certificates\Certificate;
use App\Models\certificates\Status;
use App\Http\Requests\Certificates\ListRequest;
use App\Http\Requests\Certificates\CreateRequest;
use App\Http\Requests\Certificates\UpdateCommonStepRequest;
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
