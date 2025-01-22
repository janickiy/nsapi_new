<?php

namespace App\Http\Controllers;


use App\Models\Certificates\{

};
use App\Models\References\{
    Standard,
    Hardness,
    OuterDiameter,
    Customer,
    ControlMethod,
    ControlResult,
    WallThickness,
};


use App\Http\Requests\References\{
    ListRequest,
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class ReferencesController extends Controller
{
    /**
     * Справочник стандартов
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function standardList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = Standard::search();

        $count = $q->count();
        $res = $q->limit($limit)->offset($page)->get();

        $items = Standard::map($res);

        return response()->json($items);
    }

    /**
     * Список Групп прочности
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function hardnessList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = Hardness::search();

        $count = $q->count();
        $res = $q->limit($limit)->offset($page)->get();

        $items = Hardness::map($res);

        return response()->json($items);
    }

    /**
     * Список внешних диаметров
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function outerDiameterList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = OuterDiameter::search();

        $count = $q->count();
        $res = $q->limit($limit)->offset($page)->get();

        $items = OuterDiameter::map($res);

        return response()->json($items);
    }

    /**
     * Список полкупателей
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function customerList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = Customer::search();

        $count = $q->count();
        $res = $q->limit($limit)->offset($page)->get();

        $items = Customer::map($res);

        return response()->json($items);
    }


    /**
     * Справочник методов контроля
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function controlMethodList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = ControlMethod::search();

        $res = $q->limit($limit)->offset($page)->get();
        $items = ControlMethod::map($res);

        return response()->json($items);
    }

    /**
     * Список результатов контроля
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function controlResultList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = ControlResult::search();
        $res = $q->limit($limit)->offset($page)->get();
        $items = ControlResult::map($res);

        return response()->json($items);
    }

    /**
     * Справочник толщин стенки
     *
     * @param ListRequest $request
     * @return JsonResponse
     */
    public function wallThicknessList(ListRequest $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per-page', 10);
        $page = ($page - 1) * $limit;

        $q = WallThickness::search();
        $res = $q->limit($limit)->offset($page)->get();
        $items = WallThickness::map($res);

        return response()->json($items);
    }

    /**
     * Информация по стандарту
     *
     * @param int $id
     * @return JsonResponse
     */
    public function standard(int $id): JsonResponse
    {
        $standard = Standard::find($id);

        if (!$standard) return response()->json(['error' => 'Нет стандарт с таким id!'], Response::HTTP_NOT_FOUND);

        $data = [
            'name' => $standard->name,
            'prefix' => $standard->prefix,
            'is_show_absorbed_energy' => $standard->is_show_absorbed_energy,
        ];

        return response()->json($data);
    }

    /**
     * Информация по группе прочности
     *
     * @param int $id
     * @return JsonResponse
     */
    public function hardness(int $id): JsonResponse
    {
        $hardness = Hardness::find($id);

        if (!$hardness) return response()->json(['error' => 'Нет группа прочности с таким id!'], Response::HTTP_NOT_FOUND);

        return response()->json(['name' => $hardness->name]);
    }

    /**
     * Информация по внешнему диаметру
     *
     * @param int $id
     * @return JsonResponse
     */
    public function outerDiameter(int $id): JsonResponse
    {
        $outerDiameter = OuterDiameter::find($_REQUEST['id']);

        if (!$outerDiameter) return response()->json(['error' => 'Нет внешнего диаметра с таким id!'], Response::HTTP_NOT_FOUND);

        $data = [
            'name' => $outerDiameter->name,
            'millimeter' => $outerDiameter->millimeter,
            'inch' => $outerDiameter->inch,
        ];

        return response()->json($data);
    }


}
