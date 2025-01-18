<?php

namespace App\Models\References;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Внешний диаметр
 *
 * @property integer $id
 * @property string $name Название
 * @property numeric $millimeter Диаметр в миллиметрах
 * @property numeric $inch Диаметр в дюмах
 */
class OuterDiameter extends Model
{
    protected $table = "references.outer_diameter";

    protected $fillable = [
        'name',
        'millimeter',
        'inch'
    ];

    public $timestamps = false;

    /**
     * @return Builder
     */
    public static function search(): Builder
    {
        $q = self::query()->orderBy('millimeter');

        return $q;
    }

    /**
     * @param object|null $rows
     * @return array
     */
    public static function map(?object $rows): array
    {
        $items = [];

        foreach ($rows ?? [] as $row) {
            $items[] = [
                'id'   => $row->id,
                'name' => $row->name,
                'millimeter' => $row->millimeter,
                'inch'       => $row->inch,
            ];
        }

        return $items;
    }
}
