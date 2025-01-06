<?php

namespace App\Models\certificates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Плавка
 *
 * @property integer $id
 * @property integer $certificate_id Идентификатор сертификата
 * @property string $text Текст примечания
 *
 * @property-read Certificate $certificate
 */
class Note extends Model
{
    protected $table = "certificates.note";

    protected $fillable = [
        'certificate_id',
        'text',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'id' , 'certificate_id');
    }
}
