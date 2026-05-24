<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'label_en',
        'value',
    ];

    protected $casts = [
        'value' => 'boolean',
    ];

    public static function getBool(string $key, bool $default = false): bool
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return (bool) $setting->value;
    }

    public function getLocalizationLabel(): string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? $this->label : $this->label_en;
    }
}

