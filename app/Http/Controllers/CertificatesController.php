<?php

namespace App\Http\Controllers;

use App\Models\Certificates\Certificate;
use App\Models\certificates\Status;
use App\Http\Requests\Certificates\ListRequest;
use App\Http\Requests\Certificates\CreateRequest;
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