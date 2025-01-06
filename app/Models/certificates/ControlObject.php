<?php

namespace App\Models\certificates;

use Illuminate\Database\Eloquent\Model;

/**
 * Объект контроля
 *
 * @property string $id
 * @property string $name
 */
class ControlObject extends Model
{
    protected $table = "certificates.control_object";

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;
}
