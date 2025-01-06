<?php

namespace App\Models\references;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\certificates\Certificate;

/**
 * Предел текучести
 *
 * @property integer $id
 * @property integer $standard_id Идентификатор стандарта
 * @property integer $hardness_id Идентификатор группы прочности
 * @property numeric $value_min Значение не меннее
 * @property numeric $value_max Значение не более
 *
 * @property-read Standard $standard
 * @property-read Hardness $hardness
 */
class FluidityLimit extends Model
{
    protected $table = "references.fluidity_limit";

    protected $fillable = [
        'standard_id',
        'hardness_id',
        'value_min',
        'value_max',
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
     * @param Certificate $certificate
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function findByCertificate(Certificate $certificate)
    {
        return self::query()
            ->where('standard_id', $certificate->standard_id)
            ->where('hardness_id', $certificate->hardness_id)
            ->first();
    }
}