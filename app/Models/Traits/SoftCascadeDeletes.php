<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait SoftCascadeDeletes
{
    protected function softCascadeList(): array
    {
        return (property_exists($this, 'softCascade') && is_array($this->softCascade))
            ? $this->softCascade
            : [];
    }

    public static function bootSoftCascadeDeletes(): void
    {
        // SOFT DELETE en cascada (hijos y pivots)
        static::deleting(function (Model $model) {
            foreach ($model->softCascadeList() as $relationName) {
                if (!method_exists($model, $relationName)) continue;
                $relation = $model->{$relationName}();

                // hasOne / morphOne
                if ($relation instanceof HasOne || $relation instanceof MorphOne) {
                    $child = $relation->first();
                    if ($child && in_array(SoftDeletes::class, class_uses_recursive($child))) {
                        $child->delete();
                    }
                    continue;
                }

                // hasMany / morphMany
                if ($relation instanceof HasMany || $relation instanceof MorphMany) {
                    $relation->get()->each(function ($child) {
                        if ($child && in_array(SoftDeletes::class, class_uses_recursive($child))) {
                            $child->delete();
                        }
                    });
                    continue;
                }

                // belongsToMany -> marcar pivots con deleted_at si existe la columna
                if ($relation instanceof BelongsToMany) {
                    $pivotTable = $relation->getTable();
                    $foreignKey = $relation->getForeignPivotKeyName();

                    if (Schema::hasTable($pivotTable) && Schema::hasColumn($pivotTable, 'deleted_at')) {
                        DB::table($pivotTable)
                            ->where($foreignKey, $model->getKey())
                            ->update(['deleted_at' => now()]);
                    }
                }
            }
        });
    }
}
