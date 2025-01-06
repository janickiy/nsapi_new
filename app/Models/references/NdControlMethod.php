<?php

namespace App\Models\references;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * НД на метод контроля
 *
 * @property integer $id
 * @property integer $control_method_id
 * @property string $name
 *
 * @property-read ControlMethod $controlMethod
 */
class NdControlMethod extends Model
{
    protected $table = "references.nd_control_method";

    protected $fillable = [
        'control_method_id',
        'name'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function controlMethod(): BelongsTo
    {
        return $this->belongsTo(ControlMethod::class, 'id','control_method_id');
    }
}
