<?php

namespace App\Models\References;

use App\Models\Certificates\Certificate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Толщина стенки
 *
 * @property integer $id
 * @property integer $outer_diameter_id - Идентификатор наружнего диаметра
 * @property integer $wall_thickness_id - Идентификатор толщины стенки
 * @property numeric $value - Масса
 *
 * @property-read OuterDiameter $outerDiameter
 * @property-read WallThickness $wallThickness
 */
class TheoreticalMass extends Model
{
    protected $table = "references.theoretical_mass";

    protected $fillable = [
        'outer_diameter_id',
        'wall_thickness_id',
        'value'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function outerDiameter()
    {
        return $this->belongsTo(OuterDiameter::class, 'id', 'outer_diameter_id');
    }

    /**
     * @return BelongsTo
     */
    public function wallThickness()
    {
        return $this->belongsTo(WallThickness::class, 'id', 'wall_thickness_id');
    }

    /**
     * @param WallThickness|null $wallThickness
     * @param Certificate $certificate
     * @return TheoreticalMass|null
     */
    public static function findByWallThickness(?WallThickness $wallThickness, Certificate $certificate): ?TheoreticalMass
    {
        if (!$wallThickness) {
            return null;
        }

        return self::where('wall_thickness_id', $wallThickness->id)
            ->where('outer_diameter_id', $certificate->outer_diameter_id)
            ->first();
    }
}
