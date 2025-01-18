<?php

namespace App\Models\References;

use App\Models\Certificates\Certificate;
use Illuminate\Database\Eloquent\Model;

/**
 * Поглощенная энергия
 *
 * @property integer $id
 * @property integer $standard_id Идентификатор стандарта
 * @property integer $wall_thickness_id Идентификатор толщины стенки
 * @property numeric $value Индивидуальное значение
 * @property numeric $value_average Среднее значение
 *
 * @property-read Standard $standard
 * @property-read WallThickness $wallThickness
 */
class AbsorbedEnergyLimit extends Model
{
    protected $table = "references.absorbed_energy_limit";

    protected $fillable = [
        'standard_id',
        'wall_thickness_id',
        'value',
        'value_average'
    ];

    public $timestamps = false;

    /**
     * @param WallThickness|null $wallThickness
     * @param Certificate $certificate
     * @return AbsorbedEnergyLimit|null
     */
    public static function findByWallThickness(?WallThickness $wallThickness, Certificate $certificate): ?AbsorbedEnergyLimit
    {
        if (!$wallThickness) {
            return null;
        }

        return self::where('standard_id', $certificate->standard_id)
            ->where('wall_thickness_id', $wallThickness->id)
            ->first();
    }
}
