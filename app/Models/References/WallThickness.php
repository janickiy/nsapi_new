<?php

namespace App\Models\References;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Толщина стенки
 *
 * @property integer $id
 * @property string $name Название
 * @property numeric $value Толщина
 */
class WallThickness extends Model
{
    protected $table = "references.wall_thickness";

    protected $fillable = [
        'name',
        'value'
    ];

    public $timestamps = false;

    /**
     * @return Builder
     */
    public static function search(): Builder
    {
        $q = self::query()->orderBy('value');

        if (isset($_REQUEST['name']) && $_REQUEST['name']) {
            $q->where('name', 'ILIKE', $_REQUEST['name']);
        }

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
                'id' => $row->id,
                'name' => $row->name,
                'value' => $row->value,
            ];
        }

        return $items;
    }
}
