<?php

namespace App\Models\References;

use App\Models\Certificates\Certificate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Относительное удлинение
 *
 * @property integer $id
 * @property integer $standard_id - Идентификатор стандарта
 * @property integer $hardness_id - Идентификатор группы прочности
 * @property integer $outer_diameter_id - Идентификатор наружнего диаметра
 * @property integer $wall_thickness_id - Идентификатор толщины стенки
 * @property numeric $value - Значение
 *
 * @property-read Standard $standard
 * @property-read Hardness $hardness
 * @property-read OuterDiameter $outerDiameter
 * @property-read WallThickness $wallThickness
 */
class RelativeExtension extends Model
{
    protected $table = "references.relative_extension";

    protected $fillable = [
        'standard_id',
        'hardness_id',
        'outer_diameter_id',
        'wall_thickness_id',
        'value',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function standard(): BelongsTo
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
    public function wallThickness(): BelongsTo
    {
        return $this->belongsTo(WallThickness::class, 'id', 'wall_thickness_id');
    }

    /**
     * @param WallThickness|null $wallThickness
     * @param Certificate $certificate
     * @return RelativeExtension|null
     */
    public static function findByWallThickness(?WallThickness $wallThickness, Certificate $certificate): ?RelativeExtension
    {
        if (!$wallThickness) {
            return null;
        }

        return self::where('standard_id', $certificate->standard_id)
            ->where('hardness_id', $certificate->hardness_id)
            ->where('outer_diameter_id', $certificate->outer_diameter_id)
            ->where('wall_thickness_id', $wallThickness->id)
            ->first();
    }
}
