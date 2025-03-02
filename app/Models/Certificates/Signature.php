<?php

namespace App\Models\Certificates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Плавка
 *
 * @property integer $id
 * @property integer $certificate_id Идентификатор сертификата
 * @property string $name ФИО
 * @property string $position Должность
 *
 * @property-read Certificate $certificate
 */
class Signature extends Model
{
    protected $table = "certificates.signature";

    protected $fillable = [
        'certificate_id',
        'name',
        'position'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'id', 'certificate_id');
    }
}
