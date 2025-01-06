<?php

namespace App\Models\certificates;

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
 * @property-read Certificates $certificate
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
