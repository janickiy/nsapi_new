<?php

namespace App\Models\references;

use App\Models\certificates\Certificate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HardnessLimit extends Model
{
    protected $table = "references.hardness_limit";

    protected $fillable = [
        'standard_id',
        'hardness_id',
        'value'
    ];

    public $timestamps = false;

    /**
     * @param Certificate $certificate
     * @return HardnessLimit
     */
    public static function findByCertificate(Certificate $certificate): HardnessLimit
    {
        return self::where('standard_id', $certificate->standard_id)
            ->where('hardness_id', $certificate->hardness_id)
            ->first();
    }

    /**
     * @return BelongsTo
     */
    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class, 'standard_id');
    }

    /**
     * @return BelongsTo
     */
    public function hardness(): BelongsTo
    {
        return $this->belongsTo(Hardness::class, 'hardness_id');
    }
}
