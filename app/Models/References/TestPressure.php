<?php

namespace App\Models\References;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Certificates\Certificate;

/**
 * Испытательное давление
 *
 * @property integer $id
 * @property integer $standard_id Идентификатор стандарта
 * @property integer $hardness_id Идентификатор группы прочности
 * @property integer $outer_diameter_id Идентификатор наружнего диаметра
 * @property integer $wall_thickness_id Идентификатор толщины стенки
 * @property numeric $value Толщина
 *
 * @property-read Standard $standard
 * @property-read Hardness $hardness
 * @property-read OuterDiameter $outerDiameter
 * @property-read WallThickness $wallThickness
 */
class TestPressure extends Model
{
    protected $table = "references.test_pressure";

    protected $fillable = [
        'standard_id',
        'hardness_id',
        'outer_diameter_id',
        'wall_thickness_id',
        'value'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function getStandard(): BelongsTo
    {
        return $this->belongsTo(Standard::class, 'id', 'standard_id');
    }

    /**
     * @return BelongsTo
     */
    public function hardness(): BelongsTo
    {
        return $this->belongsTo(Hardness::class, 'id', 'hardness_id');
    }

    /**
     * @return BelongsTo
     */
    public function outerDiameter(): BelongsTo
    {
        return $this->belongsTo(OuterDiameter::class, 'id', 'outer_diameter_id');
    }

    /**
     * @return BelongsTo
     */
    public function getWallThickness(): BelongsTo
    {
        return $this->belongsTo(WallThickness::class, 'id', 'wall_thickness_id');
    }

    /**
     * @param WallThickness|null $wallThickness
     * @param Certificate $certificate
     * @return TestPressure|null
     */
    public static function findByWallThickness(?WallThickness $wallThickness, Certificate $certificate): ?TestPressure
    {
        if (!$wallThickness) {
            return null;
        }

        return self::where('standard_id', $certificate->standard_id)
            ->where('hardness_id', $certificate->hardness_id)
            ->where('wall_thickness_id', $wallThickness->id)
            ->where('outer_diameter_id', $certificate->outer_diameter_id)
            ->first();
    }
}
