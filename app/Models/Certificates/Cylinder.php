<?php

namespace App\Models\Certificates;

use Illuminate\Database\Eloquent\Model;

/**
 * Плавка
 *
 * @property integer $id
 * @property string $material Материал изготовления
 * @property numeric $weight Масса барабана
 * @property numeric $diameter_core Диаметр сердечника
 * @property numeric $diameter_cheek Диаметр щек
 * @property numeric $width Ширина
 * @property string $mark_nitrogen Отметка о заполнении азотом
 *
 * @property-read Certificate $certificate
 */
class Cylinder extends Model
{
    protected $table = "certificates.cylinder";

    protected $fillable = [
        'id',
        'material',
        'weight',
        'diameter_core',
        'diameter_cheek',
        'mark_nitrogen'
    ];

    public $timestamps   = false;
    public $incrementing = false;
}
