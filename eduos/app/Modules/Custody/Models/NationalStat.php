<?php

namespace App\Modules\Custody\Models;

use Illuminate\Database\Eloquent\Model;

class NationalStat extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value', 'delta_pct'];

    public static function get(string $key): ?self
    {
        return static::find($key);
    }
}
