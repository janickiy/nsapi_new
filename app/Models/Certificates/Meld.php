<?php

namespace App\Models\Certificates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Плавка
 *
 * @property integer $id
 * @property integer $certificate_id Идентификатор сертификата
 * @property string $number Номер плавки
 * @property numeric $sekv СЭКВ, %
 * @property string $sekv_note Сноска для СЭКВ
 * @property numeric $chemical_c Химический состав углерод C, %
 * @property numeric $chemical_mn Химический состав марганец Mn, %
 * @property numeric $chemical_si Химический состав кремний Si, %
 * @property numeric $chemical_s Химический состав сера S, %
 * @property numeric $chemical_p Химический состав фосфор P, %
 * @property numeric $dirty_type_a Степень загрязненности Тип A
 * @property numeric $dirty_type_b Степень загрязненности Тип B
 * @property numeric $dirty_type_c Степень загрязненности Тип C
 * @property numeric $dirty_type_d Степень загрязненности Тип D
 * @property numeric $dirty_type_ds Степень загрязненности Тип DS
 *
 * @property-read Certificate $certificate
 * @property-read Roll[] $rolls
 */
class Meld extends Model
{
    protected $table = "certificates.meld";

    protected $fillable = [
        'certificate_id',
        'number',
        'sekv',
        'sekv_note',
        'chemical_c',
        'chemical_mn',
        'chemical_si',
        'chemical_s',
        'chemical_p',
        'dirty_type_a',
        'dirty_type_b',
        'dirty_type_c',
        'dirty_type_d',
        'dirty_type_ds',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    /**
     * @return HasMany
     */
    public function rolls(): HasMany
    {
        return $this->hasMany(Roll::class, 'meld_id' ,'id')->orderBy('serial_number');
    }
}
