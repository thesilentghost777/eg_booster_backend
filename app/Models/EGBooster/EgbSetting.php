<?php

namespace App\Models\EGBooster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EgbSetting extends Model
{
    protected $table = 'egb_settings';

    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("egb_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general', string $label = ''): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'group' => $group,
                'label' => $label ?: $key,
            ]
        );

        Cache::forget("egb_setting_{$key}");
        return $setting;
    }

    public static function getByGroup(string $group)
    {
        return self::where('group', $group)->get();
    }
}
