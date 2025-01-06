<?php

namespace App\Models\references;

use App\Models\certificates\Certificate;
use Illuminate\Database\Eloquent\Model;

/**
 * Толщина стенки
 *
 * @property integer $id
 * @property integer $hardness_id Идентификатор группы прочности
 * @property numeric $carbon Углерод C
 * @property numeric $manganese Марганец Mn
 * @property numeric $silicon Кремний Si
 * @property numeric $sulfur Сера S
 * @property numeric $phosphorus Фосфор P
 *
 * @property-read Hardness $hardness
 */
class MassFraction extends Model
{
    protected $table = "references.mass_fraction";

    protected $fillable = [
        'hardness_id',
        'carbon',
        'manganese',
        'silicon',
        'sulfur',
        'phosphorus',
    ];

    public $timestamps = false;

    /**
     * @param Certificate $certificate
     * @return \Illuminate\Database\Eloquent\Builder|Model|object|null
     */
    public static function findByCertificate(Certificate $certificate): ?MassFraction
    {
        return self::query()
            ->where('hardness_id', $certificate->hardness_id)
            ->first();
    }
}
