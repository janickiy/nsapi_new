<?php

namespace App\Models\references;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Метод контроля
 *
 * @property integer $id
 * @property string $text
 */
class ControlResult extends Model
{
    protected $table = "references.control_result";

    protected $fillable = [
        'text'
    ];

    public $timestamps = false;

    /**
     * @return Builder
     */
    public static function search(): Builder
    {
        $q = self::query()->orderBy('text');

        if (isset($_REQUEST['text']) && $_REQUEST['text']) {
            $q->where('text', 'ILIKE', $_REQUEST['text']);
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
                'nd_control_method' => $row->nd_control_method,
            ];
        }

        return $items;
    }
}
