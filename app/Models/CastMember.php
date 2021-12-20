<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, Traits\Uuid;

    public const TYPE_DIRECTOR = 0;
    public const TYPE_ACTOR = 1;

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'type' => 'int'
    ];
    protected $keyType = 'string';
    public $incrementing = false;
}
