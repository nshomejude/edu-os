<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'app_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, string $default = ''): string
    {
        static $cache = [];
        if (! array_key_exists($key, $cache)) {
            $cache[$key] = static::find($key)?->value ?? $default;
        }

        return $cache[$key];
    }

    public static function put(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
