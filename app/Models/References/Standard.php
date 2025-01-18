<?php

namespace App\Models\References;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Yiisoft\Arrays\ArrayHelper;

/**
 * Стандарт
 *
 * @property integer $id
 * @property string $name
 * @property string $prefix
 * @property boolean $is_show_absorbed_energy
 */
class Standard extends Model
{
    protected $table = "references.standard";

    protected $fillable = [
        'name',
        'prefix',
        'is_show_absorbed_energy',
    ];

    public $timestamps = false;

    /**
     * @return Builder
     */
    public static function search(): Builder
    {
        $q = self::query()->orderBy('name', 'DESC');

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
            ];
        }

        return $items;
    }

    /**
     * @return array
     */
    public static function getAllToList(): array
    {
        return ArrayHelper::map(self::query()->orderBy('name')->get(), 'id', 'name');
    }
}
