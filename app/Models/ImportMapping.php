<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportMapping extends Model
{
    protected $fillable = [
        'type',
        'raw_label',
        'target_id',
        'target_name',
    ];

    /**
     * Resolve target ID for a given type and raw label.
     */
    public static function resolve(string $type, string $rawLabel): ?int
    {
        $normalized = strtolower(trim($rawLabel));
        if (empty($normalized)) {
            return null;
        }

        $mapping = self::where('type', $type)
            ->where('raw_label', $normalized)
            ->first();

        return $mapping ? $mapping->target_id : null;
    }

    /**
     * Register or update a mapping.
     */
    public static function register(string $type, string $rawLabel, int $targetId, ?string $targetName = null): self
    {
        $normalized = strtolower(trim($rawLabel));
        return self::updateOrCreate(
            ['type' => $type, 'raw_label' => $normalized],
            ['target_id' => $targetId, 'target_name' => $targetName]
        );
    }
}
