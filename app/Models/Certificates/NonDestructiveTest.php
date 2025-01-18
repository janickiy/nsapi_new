<?php

namespace App\Models\Certificates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Неразрушающий контроль
 *
 * @property integer $id
 * @property integer $certificate_id Идентификатор сертификата
 * @property string $control_object_id Идентификатор объекта контроля
 * @property integer $control_method_id Идентификатор метода контроля
 * @property integer $nd_control_method_id Идентификатор НД на метод контроля
 * @property integer $control_result_id Идентификатор результата контроля
 * @property string $note Текст сноски
 *
 * @property-read Certificate $certificate
 * @property-read ControlObject $controlObject
 * @property-read ControlMethod $controlMethod
 * @property-read NdControlMethod $ndControlMethod
 * @property-read ControlResult $controlResult
 */
class NonDestructiveTest extends Model
{
    protected $table = "certificates.non_destructive_test";

    protected $fillable = [
        'certificate_id',
        'control_object_id',
        'control_method_id',
        'nd_control_method_id',
        'control_result_id',
        'note',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'id' , 'certificate_id');
    }

    /**
     * @return BelongsTo
     */
    public function controlMethod(): BelongsTo
    {
        return $this->belongsTo(ControlMethod::class, 'id' ,'control_method_id');
    }
}
