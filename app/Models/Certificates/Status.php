<?php

namespace App\Models\Certificates;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    const STATUS_DRAFT     = 'draft';
    const STATUS_APPROVE   = 'approve';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REFUNDED  = 'refunded';
    const STATUS_DELETED   = 'deleted';

    protected $table = "certificates.status";

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;
}
