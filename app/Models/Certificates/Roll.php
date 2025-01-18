<?php

namespace App\Models\Certificates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Рулон
 *
 * @property integer $id
 * @property integer $meld_id Идентификатор плавки
 * @property string $number Номер рулона
 * @property integer $wall_thickness_id Идентификатор толщины стенки
 * @property numeric $length Длина рулона
 * @property string $location_seams_note Сноска для расположения швов
 * @property numeric $grain_size Размер зерна
 * @property string $hardness_note Сноска для твердости
 * @property boolean $is_exact_hardness_om Точное или нет значение твердости ОМ
 * @property numeric $hardness_om Твердтость OM
 * @property boolean $is_exact_hardness_ssh Точное или нет значение твердости СШ
 * @property numeric $hardness_ssh Твердтость СШ
 * @property boolean $is_exact_hardness_ztv Точное или нет значение твердости ЗТВ
 * @property numeric $hardness_ztv Твердтость ЗТВ
 * @property boolean $is_use_note Использовать сноску
 * @property numeric $absorbed_energy_1 Поглощенная энергия образец 1
 * @property numeric $absorbed_energy_2 Поглощенная энергия образец 2
 * @property numeric $absorbed_energy_3 Поглощенная энергия образец 3
 * @property numeric $fluidity Предел текучести
 * @property string $fluidity_note Сноска для предела текучести
 * @property numeric $strength Предел прочности
 * @property numeric $relative_extension Относительное удлинение
 * @property string $relative_extension_note Сноска для относительного удлинения
 * @property boolean $is_fill_testing Заполнены результаты тестирования
 * @property integer $serial_number Порядковый номер
 * @property numeric $test_pressure_min Минимальное испытательное давление
 * @property numeric $transverse_seams_location Расположения поперечных швов рулонов от начала ГНКТ, м
 * @property numeric $theoretical_mass Теоретическая масса 1 метра ГНКТ, кг
 *
 * @property-read numeric $locationSeams Расположения поперечных швов от начала
 * @property-read Meld $meld
 */
class Roll extends Model
{
    protected $table = "certificates.roll";

    protected $fillable = [
        'number',
        'meld_id',
        'number_character',
        'wall_thickness_id',
        'length',
        'location_seams_note',
        'grain_size',
        'hardness_note',
        'hardness_om',
        'hardness_ssh',
        'hardness_ztv',
        'is_use_note',
        'absorbed_energy_1',
        'absorbed_energy_2',
        'absorbed_energy_3',
        'fluidity',
        'fluidity_note',
        'strength',
        'relative_extension',
        'relative_extension_note',
        'is_fill_testing',
        'serial_number',
        'is_exact_hardness_om',
        'is_exact_hardness_ssh',
        'is_exact_hardness_ztv',
        'test_pressure_min',
        'transverse_seams_location',
        'theoretical_mass',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function meld(): BelongsTo
    {
        return $this->belongsTo(Meld::class, 'id', 'meld_id');
    }

    /**
     * @return BelongsTo
     */
    public function wallThickness(): BelongsTo
    {
        return $this->belongsTo(WallThickness::class, 'wall_thickness_id', 'id');
    }

    /**
     * @return float|null
     */
    public function getLocationSeams(): ?float
    {
        $result = 0;
        $certificateRolls = $this->meld->certificate->rolls;
        if ($certificateRolls) {
            if ($certificateRolls[count($certificateRolls) - 1]->id === $this->id) {
                return null;
            }
            foreach ($certificateRolls as $roll) {
                if ($roll->length) {
                    $result += $roll->length;
                } else {
                    $result = 0;
                    break;
                }
                if ($roll->id === $this->id) {
                    break;
                }
            }
        }
        return $result ?: null;
    }

    /**
     * Обновление сортировки рулонов
     *
     * @param Certificate $certificate
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public static function updateSort(Certificate $certificate, array $data): void
    {
        foreach ($certificate->rolls as $roll) {
            $sort = array_search($roll->id, $data);
            if ($sort !== false) {
                $roll->serial_number = $sort + 1;
                $roll->save();
            } else {
                throw new \Exception('Не все рулоны указаны');
            }
        }
    }
}
