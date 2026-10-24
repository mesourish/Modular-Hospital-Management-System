<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'hospital_id',
        'branch_id',
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Retrieve a setting value with fallback hierarchy:
     * 1. Branch-specific override
     * 2. Hospital-wide setting
     * 3. Global platform default (hospital_id = null, branch_id = null)
     * 4. Provided $default fallback
     */
    public static function get(string $key, $default = null, ?int $branchId = null, ?int $hospitalId = null)
    {
        // 1. Branch specific
        if ($branchId) {
            $setting = static::where('key', $key)
                ->where('branch_id', $branchId)
                ->first();
            if ($setting && $setting->value !== null && $setting->value !== '') {
                return static::castValue($setting->value, $setting->type);
            }
        }

        // 2. Hospital wide
        if ($hospitalId) {
            $setting = static::where('key', $key)
                ->where('hospital_id', $hospitalId)
                ->whereNull('branch_id')
                ->first();
            if ($setting && $setting->value !== null && $setting->value !== '') {
                return static::castValue($setting->value, $setting->type);
            }
        }

        // 3. Global platform fallback
        $global = static::where('key', $key)
            ->whereNull('hospital_id')
            ->whereNull('branch_id')
            ->first();

        if ($global && $global->value !== null && $global->value !== '') {
            return static::castValue($global->value, $global->type);
        }

        return $default;
    }

    /**
     * Set / Update a setting value for a given scope
     */
    public static function set(string $key, $value, ?int $branchId = null, ?int $hospitalId = null, string $group = 'general', string $type = 'string'): self
    {
        $query = static::where('key', $key);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id');
        }

        if ($hospitalId) {
            $query->where('hospital_id', $hospitalId);
        } else {
            $query->whereNull('hospital_id');
        }

        $setting = $query->first();

        if (! $setting) {
            $setting = new static;
            $setting->key = $key;
            $setting->hospital_id = $hospitalId;
            $setting->branch_id = $branchId;
            $setting->group = $group;
            $setting->type = $type;
        }

        $setting->value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        $setting->save();

        return $setting;
    }

    /**
     * Return all settings as key-value pairs for a specific context
     */
    public static function getAllMapped(?int $hospitalId = null, ?int $branchId = null): array
    {
        $keys = static::distinct()->pluck('key');
        $mapped = [];

        foreach ($keys as $key) {
            $mapped[$key] = static::get($key, null, $branchId, $hospitalId);
        }

        return $mapped;
    }

    /**
     * Type casting utility
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true) ?? [],
            default => (string) $value,
        };
    }
}
