<?php

namespace App\Models\references;

use Yiisoft\Arrays\ArrayHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Метод контроля
 *
 * @property integer $id
 * @property string $name
 *
 * @property-read NdControlMethod[] $ndControlMethods
 */
class ControlMethod extends Model
{
    protected $table = "references.control_method";

    protected $fillable = [
        'name'
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

        foreach ($rows as $row) {
            $items[] = [
                'id' => $row->id,
                'name' => $row->name,
                'nd_control_method' => $row->nd_control_method,
            ];
        }

        return $items;
    }

    /**
     * @return HasMany
     */
    public function getNdControlMethods(): HasMany
    {
        return $this->hasMany(NdControlMethod::class, 'control_method_id' , 'id');
    }

    /**
     * @return array
     */
    public static function getAllToList(): array
    {
        return ArrayHelper::map(self::query()->orderBy('name')->get(), 'id', 'name');
    }
}
