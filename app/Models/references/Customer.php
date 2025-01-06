<?php

namespace App\Models\references;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Покупатель
 *
 * @property integer $id
 * @property string $name
 */
class Customer extends Model
{
    protected $table = "references.customer";

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    /**
     * @return Builder
     */
    public static function search(): Builder
    {
        $q = self::query()->orderBy('name');

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
}
