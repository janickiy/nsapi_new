<?php

namespace App\Models\certificates;

use Illuminate\Contracts\Validation\Validator;
use App\Models\references\{
    Hardness,
    Customer,
    NdControlMethod,
    ControlResult,
    Standard,
    TheoreticalMass,
    OuterDiameter,
    TestPressure,
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Сертификат
 *
 * @property integer $id
 * @property string $status_id Идентификатор статусы
 * @property string $number Номер документа
 * @property string $number_tube Номер трубы
 * @property string $rfid RFID метка
 * @property string $product_type Вид изделия
 * @property integer $standard_id Идентификатор стандарта
 * @property integer $hardness_id Идентификатор группы прочности
 * @property integer $outer_diameter_id Идентификатор внешнего диаметра
 * @property string $agreement_delivery Договор поставки
 * @property string $type_heat_treatment Вид термической обработки
 * @property string $type_quick_connection Тип быстросъемного соединения
 * @property string $type_quick_connection_note Сноска для типа быстросъемного соединения
 * @property string $result_hydrostatic_test Результат гидростатических испытаний
 * @property string $brand_geophysical_cable Марка геофизического кабеля
 * @property string $brand_geophysical_cable_note Сноска для типа марки геофизического кабеля
 * @property numeric $length_geophysical_cable Длина геофизического кабеля
 * @property string $length_geophysical_cable_note Сноска для длины геофизического кабеля
 * @property string $customer Покупатель
 * @property string $created_at Дата создания
 * @property string $rolls_note Сноска для рулонов
 * @property numeric $gnkt_wall_depth толщина стенки ГНКТ
 */
class Certificate extends Model
{
    const DIRTY_MAX = 2;
    const GRAIN_MAX = 8;

    public $timestamps = false;

    protected $table = "certificates.certificate";

    protected $fillable = [
        'status_id',
        'number',
        'number_tube',
        'rfid',
        'product_type',
        'standard_id',
        'hardness_id',
        'outer_diameter_id',
        'agreement_delivery',
        'type_heat_treatment',
        'type_quick_connection',
        'type_quick_connection_note',
        'result_hydrostatic_test',
        'brand_geophysical_cable',
        'length_geophysical_cable',
        'customer',
        'brand_geophysical_cable_note',
        'length_geophysical_cable_note',
        'rolls_note',
        'gnkt_wall_depth',
        'created_at',
    ];

    /**
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'id', 'status_id');
    }

    /**
     * @return HasMany
     */
    public function melds(): HasMany
    {
        return $this->hasMany(Meld::class, 'certificate_id', 'id');
    }

    /**
     * @param array $status
     * @return Builder
     */
    public static function search(array $status): Builder
    {
        $q = self::query()
            ->select('certificate.*', 's.name as status')
            ->whereIn('certificate.status_id', $status)
            ->distinct()
            ->leftJoin('certificates.meld as m', 'certificate.id', '=', 'm.certificate_id')
            ->leftJoin('certificates.roll as r', 'r.meld_id', '=', 'm.id')
            ->leftJoin('certificates.status as s', 'certificate.status_id', '=', 's.id')
            ->orderBy('certificate.id', 'DESC')
            ->groupBy('certificate.id')
            ->groupBy('s.id')
            ->groupBy('m.id')
            ->groupBy('r.id');

        $q->where(function ($query) {
            if (isset($_REQUEST['number']) && $_REQUEST['number']) {
                $query->where('certificate.number', 'ILIKE', $_REQUEST['number']);
            }
            if (isset($_REQUEST['number_tube']) && $_REQUEST['number_tube']) {
                $query->where('certificate.number_tube', 'ILIKE', $_REQUEST['number_tube']);
            }
            if (isset($_REQUEST['rfid']) && $_REQUEST['rfid']) {
                $query->where('certificate.rfid', 'ILIKE', $_REQUEST['rfid']);
            }
            if (isset($_REQUEST['outer_diameter_id']) && $_REQUEST['outer_diameter_id']) {
                $query->where('certificate.outer_diameter_id', $_REQUEST['outer_diameter_id']);
            }
            if (isset($_REQUEST['created_at']) && $_REQUEST['created_at']) {
                $query->where('certificate.created_at', $_REQUEST['created_at']);
            }
        });

        if (isset($_REQUEST['search_string']) && $_REQUEST['search_string']) {
            $q->where(function ($query) {
                $query->orWhere('certificate.number', 'ILIKE', $_REQUEST['search_string']);
                $query->orWhere('certificate.number_tube', 'ILIKE', $_REQUEST['search_string']);
                $query->orWhere('certificate.rfid', 'ILIKE', $_REQUEST['search_string']);
                $query->orWhere('certificate.customer', 'ILIKE', $_REQUEST['search_string']);
                $query->orWhere('certificate.product_type', 'ILIKE', $_REQUEST['search_string']);
            });
        }

        if (isset($_REQUEST['length_min']) && $_REQUEST['length_min']) {
            $q->having('COALESCE(SUM(r.length), 0)', '>=', $_REQUEST['length_min']);
        }

        if (isset($_REQUEST['length_max']) && $_REQUEST['length_max']) {
            $q->having('COALESCE(SUM(r.length), 0)', '<=', $_REQUEST['length_max']);
        }

        return $q;
    }

    /**
     * @param object|null $rows
     * @return array
     */
    public static function map(?object $rows): array
    {
        $items = [];

        foreach ($rows ?? [] as $row) {
            $items[] = [
                'id' => $row->id,
                'status_id' => $row->status_id,
                'number' => $row->number,
                'number_tube' => $row->number_tube,
                'rfid' => $row->rfid,
                'product_type' => $row->product_type,
                'gnkt_wall_depth' => $row->gnkt_wall_depth,
                'created_at' => $row->created_at,
                'status' => [
                    'id' => $row->status_id,
                    'name' => $row->status,
                ],
            ];
        }

        return $items;
    }

    /**
     * @return HasMany
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'certificate_id', 'id');
    }


    /**
     * @return array
     */
    public function getNonDestructiveTestsAsArray(): array
    {
        $result = [];
        $i = 1;
        foreach ($this->nonDestructiveTests as $controlObjects) {
            $result[$controlObjects->control_object_id]["method_$i"] = [
                "control_method_id" => $controlObjects->control_method_id,
                "nd_control_method_id" => $controlObjects->nd_control_method_id,
                "control_result_id" => $controlObjects->control_result_id,
                "note" => $controlObjects->note,
            ];
            $i++;
        }

        return $result;
    }

    /**
     * @return HasMany
     */
    public function nonDestructiveTests(): HasMany
    {
        return $this->hasMany(NonDestructiveTest::class, 'certificate_id', 'id')
            ->orderBy('control_object_id')
            ->orderBy('control_method_id');
    }

    public function getNotesAsArray(): array
    {
        $result = [];
        foreach ($this->notes as $note) {
            $result[$note->id] = $note;
        }

        return $result;
    }

    /**
     * @return void
     */
    public function scopeRemove(): void
    {
        if ($this->status_id === Status::STATUS_PUBLISHED) {
            $this->status_id = Status::STATUS_DELETED;
            $this->save();
        } else {
            $this->delete();
        }
    }

    /**
     * Восстановление удаленного сертификата в черновики
     *
     * @return void
     */
    public function scopeRestore(): void
    {
        if ($this->status_id === Status::STATUS_DELETED) {
            $this->status_id = Status::STATUS_DRAFT;
            $this->save();
        }
    }

    /**
     * Возвращение на доработку
     *
     * @return void
     */
    public function scopeRefund(): void
    {
        if ($this->status_id === Status::STATUS_APPROVE) {
            $this->status_id = Status::STATUS_REFUNDED;
            $this->save();
        }
    }

    /**
     * @return array
     */
    public function getDetailTube(): array
    {
        $melds = [];
        foreach ($this->melds as $meld) {
            $melds[$meld->id] = $meld->toArray();
        }

        return [
            'rolls_note' => $this->rolls_note,
            'melds' => $melds,
        ];
    }

    public function getRolls(): array
    {
        $rolls = [];

        foreach ($this->melds as $meld) {
            $rolls[] = $meld->rolls;

        }

        return $rolls;
    }

    /**
     * @return HasMany
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class, 'certificate_id', 'id');
    }


    /**
     * @return HasOne
     */
    public function cylinder(): HasOne
    {
        return $this->hasOne(Cylinder::class, 'id', 'id');
    }

    /**
     * @return array
     */
    public
    function getSignaturesAsArray(): array
    {
        $result = [];
        foreach ($this->signatures as $signature) {
            $result[$signature->id] = $signature;
        }
        return $result;
    }

    /**
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function saveNonDestructiveTest(array $data): void
    {
        NonDestructiveTest::where('certificate_id', $this->id)->delete();

        foreach (['method_1', 'method_2', 'method_3'] as $method) {
            $controlObjectId = "cross_seams_roll_ends";
            $itemData = $data[$controlObjectId][$method] ?? null;

            if ($itemData) $this->saveNonDestructiveTestItem($itemData, $controlObjectId);
        }
    }

    /**
     * @param array $data
     * @param string $controlObjectId
     * @return void
     * @throws \Exception
     */
    private function saveNonDestructiveTestItem(array $data, string $controlObjectId): void
    {
        $rules = [
            'control_method_id' => 'integer',
            'nd_control_method_id' => 'integer',
            'control_result_id' => 'integer',
        ];

        StringHelper::validate($data, $rules);

        $controlResult = ControlResult::find($data['control_result_id']);

        if (!$controlResult) throw new \Exception('Значение control_result_id должно существовать', 404);

        $ndControlMethod = NdControlMethod::where('control_method_id', $data['control_method_id'])->first();

        if (!$ndControlMethod) throw new \Exception('Значение nd_control_method_id должно существовать', 404);

        $controlObject = ControlObject::find($controlObjectId);

        if (!$controlObject) throw new \Exception('Значение control_object_id должно существовать', 404);

        NonDestructiveTest::create(array_merge($data, ['certificate_id' => $this->id, 'control_object_id' => $controlObjectId]));
    }

    /**
     * Сохранение детальной информации о трубе
     *
     * @param array $data
     * @param int $certificate_id
     * @return void
     * @throws \Exception
     */
    public function saveDetailTube(array $data, int $certificate_id): void
    {
        foreach ($data['melds'] ?? [] as $meldId => $meldData) {
            $meldData = is_array($meldData) ? $meldData : [];
            $meld = Meld::find(intval($meldId));

            if ($meld) {



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

                StringHelper::validate($meldData, $rules);

                $meld->number = $meldData['number'] ?? null;
                $meld->certificate_id = $certificate_id;
                $meld->sekv = $meldData['sekv'] ?? null;
                $meld->sekv_note = $meldData['sekv_note'] ?? null;
                $meld->chemical_c = $meldData['chemical_c'] ?? null;
                $meld->chemical_mn = $meldData['chemical_mn'] ?? null;
                $meld->chemical_si = $meldData['chemical_si'] ?? null;
                $meld->chemical_s = $meldData['chemical_s'] ?? null;
                $meld->chemical_p = $meldData['chemical_p'] ?? null;
                $meld->dirty_type_a = $meldData['dirty_type_a'] ?? null;
                $meld->dirty_type_b = $meldData['dirty_type_b'] ?? null;
                $meld->dirty_type_c = $meldData['dirty_type_d'] ?? null;
                $meld->dirty_type_ds = $meldData['dirty_type_ds'] ?? null;
                $meld->save();

                foreach ($meldData['rolls'] ?? [] as $rollId => $rollData) {
                    $rollData = is_array($rollData) ? $rollData : [];
                    $roll = Roll::find(intval($rollId));

                    if ($roll) {
                        $rules = [
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

                        StringHelper::validate($rollData, $rules);

                        $roll->number = $rollData['number'] ?? null;
                        $roll->wall_thickness_id = $rollData['wall_thickness_id'] ?? null;
                        $roll->length = $rollData['length'] ?? null;
                        $roll->location_seams_note = $rollData['location_seams_note'] ?? null;
                        $roll->grain_size = $rollData['grain_size'] ?? null;
                        $roll->hardness_note = $rollData['hardness_note'] ?? null;
                        $roll->is_exact_hardness_om = $rollData['is_exact_hardness_om'] ?? null;
                        $roll->hardness_om = $rollData['hardness_om'] ?? null;
                        $roll->is_exact_hardness_ssh = $rollData['is_exact_hardness_ssh'] ?? null;
                        $roll->hardness_ssh = $rollData['hardness_ssh'] ?? null;
                        $roll->is_exact_hardness_ztv = $rollData['is_exact_hardness_ztv'] ?? null;
                        $roll->hardness_ztv = $rollData['hardness_ztv'] ?? null;
                        $roll->is_use_note = $rollData['is_use_note'] ?? null;
                        $roll->is_fill_testing = $rollData['is_fill_testing'] ?? null;
                        $roll->absorbed_energy_1 = $rollData['absorbed_energy_1'] ?? null;
                        $roll->absorbed_energy_2 = $rollData['absorbed_energy_2'] ?? null;
                        $roll->absorbed_energy_3 = $rollData['absorbed_energy_3'] ?? null;
                        $roll->fluidity = $rollData['fluidity'] ?? null;
                        $roll->fluidity_note = $rollData['fluidity_note'] ?? null;
                        $roll->strength = $rollData['strength'] ?? null;
                        $roll->relative_extension = $rollData['relative_extension'] ?? null;
                        $roll->relative_extension_note = $rollData['relative_extension_note'] ?? null;
                        $roll->save();
                    }
                }
            }
        }
    }

    /**
     * @param string $customerName
     * @return void
     */
    private function saveCustomer(string $customerName): void
    {
        $customer = Customer::where('name', $customerName)->first();

        if (!$customer) {
            Customer::create(['name' => $customerName]);
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function saveCommon(array $data)
    {
        $rules = [
            'number' => 'required',
            'number_tube' => 'required',
            'standard_id' => 'integer',
            'hardness_id' => 'integer',
            'outer_diameter_id' => 'integer',
            'length_geophysical_cable' => 'integer',
        ];

        StringHelper::validate($data, $rules);

        $standard = Standard::find(intval($data['standard_id']));

        if (!$standard) throw new \Exception('Нет стандарта с таким standard_id!', 404);

        $hardness = Hardness::find(intval($data['hardness_id']));

        if (!$hardness) throw new \Exception('Нет группа прочности с таким hardness_id!', 404);

        $outerDiameter = OuterDiameter::find(intval($data['outer_diameter_id']));

        if (!$outerDiameter) throw new \Exception('Нет внешний диаметр с таким outer_diameter_id!', 404);

        $this->number = $data['number'];
        $this->number_tube = $data['number_tube'];
        $this->rfid = $data['rfid'];
        $this->created_at = $data['created_at'];
        $this->product_type = $data['product_type'];
        $this->standard_id = $data['standard_id'];
        $this->hardness_id = $data['hardness_id'];
        $this->outer_diameter_id = $data['outer_diameter_id'];
        $this->agreement_delivery = $data['agreement_delivery'];
        $this->type_heat_treatment = $data['type_heat_treatment'];
        $this->type_quick_connection = $data['type_quick_connection'];
        $this->type_quick_connection_note = $data['type_quick_connection_note'];
        $this->result_hydrostatic_test = $data['result_hydrostatic_test'];
        $this->brand_geophysical_cable = $data['brand_geophysical_cable'];
        $this->brand_geophysical_cable_note = $data['brand_geophysical_cable_note'];
        $this->length_geophysical_cable = $data['length_geophysical_cable'];
        $this->length_geophysical_cable_note = $data['length_geophysical_cable_note'];
        $this->customer = $data['customer'];
        $this->save();

        if ($this->customer) {
            $this->saveCustomer($this->customer);
        }
    }

    /**
     * Обновление подписей
     *
     * @param array $data
     * @return void
     */
    public function saveSignatures(array $data): void
    {
        foreach ($data as $signatureId => $signatureData) {
            $signatureData = is_array($signatureData) ? $signatureData : [];
            $signature = Signature::find(intval($signatureId));

            if ($signature) {
                $signature->name = $signatureData['name'];
                $signature->position = $signatureData['position'];
                $signature->save();
            }
        }
    }


    /**
     * Сохранение информации о барабане
     *
     * @param array $data
     * @return void
     */
    public function saveCylinder(array $data): void
    {
        if ($this->cylinder) {
            $this->cylinder->material = $data['material'];
            $this->cylinder->weight = $data['weight'];
            $this->cylinder->diameter_core = $data['diameter_core'];
            $this->cylinder->diameter_cheek = $data['diameter_cheek'];
            $this->cylinder->width = $data['width'];
            $this->cylinder->mark_nitrogen = $data['mark_nitrogen'];
            $this->cylinder->save();
        } else {
            Cylinder::create($data);
        }
    }

    /**
     * @return HasOne
     */
    public function outerDiameter(): HasOne
    {
        return $this->hasOne(OuterDiameter::class, 'id', 'outer_diameter_id');
    }

    /**
     * @return HasOne
     */
    public function standard(): HasOne
    {
        return $this->hasOne(Standard::class, 'id', 'standard_id');
    }

    /**
     * @return HasOne
     */
    public function hardness(): HasOne
    {
        return $this->hasOne(Hardness::class, 'id', 'hardness_id');
    }

    /**
     * Давление испытательное
     *
     * @return float
     */
    public function getPressureTest(): float
    {
        $result = [];
        foreach ($this->melds as $meld) {
            foreach ($meld->rolls as $roll) {
                if ($roll->wallThickness) {
                    $testPressure = TestPressure::findByWallThickness($roll->wallThickness, $this);
                    if ($testPressure) {
                        $result[] = $testPressure->value;
                    }
                }
            }
        }
        return $result ? min($result) : 0;
    }

    /**
     * Масса теоретическая
     *
     * @return float
     */
    public function getTheoreticalMass(): float
    {
        $result = 0;
        foreach ($this->melds as $meld) {
            foreach ($meld->rolls as $roll) {
                if ($roll->wallThickness) {
                    $theoreticalMass = TheoreticalMass::findByWallThickness($roll->wallThickness, $this);
                    if ($theoreticalMass && $roll->length) {
                        $result += ($theoreticalMass->value * $roll->length);
                    }
                }
            }
        }
        return round($result, 3);
    }

    /**
     * @return array
     */
    public function getCertificateArray(): array
    {
        return [
            'status_id' => $this->status_id,
            'number' => $this->number,
            'number_tube' => $this->number_tube,
            'rfid' => $this->rfid,
            'created_at' => $this->created_at,
            'product_type' => $this->product_type,
            'standard' => $this->standard,
            'hardness' => $this->hardness,
            'outer_diameter' => $this->outerDiameter,
            'agreement_delivery' => $this->agreement_delivery,
            'type_heat_treatment' => $this->type_heat_treatment,
            'type_quick_connection' => $this->type_quick_connection,
            'type_quick_connection_note' => $this->type_quick_connection_note,
            'result_hydrostatic_test' => $this->result_hydrostatic_test,
            'brand_geophysical_cable' => $this->brand_geophysical_cable,
            'brand_geophysical_cable_note' => $this->brand_geophysical_cable_note,
            'length_geophysical_cable' => $this->length_geophysical_cable,
            'customer' => $this->customer,
            'rolls_note' => $this->rolls_note,
            'gnkt_wall_depth' => $this->gnkt_wall_depth
        ];
    }
}
