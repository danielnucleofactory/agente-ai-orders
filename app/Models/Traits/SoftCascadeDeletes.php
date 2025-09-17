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
    /**
     * El modelo que use este trait debe definir:
     *   protected array $softCascade = ['items', 'documents', ...];
     * con los nombres de sus relaciones a cascadear.
     */
    public static function bootSoftCascadeDeletes(): void
    {
        /**
         * ---- Cascade al ELIMINAR (soft delete) ----
         */
        static::deleting(function (Model $model) {
            // Si no es soft delete, no tocamos nada aquí.
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            $relations = property_exists($model, 'softCascade') && is_array($model->softCascade)
                ? $model->softCascade
                : [];

            foreach (array_unique($relations) as $name) {
                if (!method_exists($model, $name)) {
                    continue;
                }

                $relation = $model->{$name}();

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

        /**
         * ---- Cascade al RESTAURAR (soft restore) ----
         */
        static::restoring(function (Model $model) {
            $relations = property_exists($model, 'softCascade') && is_array($model->softCascade)
                ? $model->softCascade
                : [];

            foreach (array_unique($relations) as $name) {
                if (!method_exists($model, $name)) {
                    continue;
                }

                $relation = $model->{$name}();

                // hasOne / morphOne
                if ($relation instanceof HasOne || $relation instanceof MorphOne) {
                    $child = $relation->withTrashed()->first();
                    if ($child && in_array(SoftDeletes::class, class_uses_recursive($child))) {
                        $child->restore();
                    }
                    continue;
                }

                // hasMany / morphMany
                if ($relation instanceof HasMany || $relation instanceof MorphMany) {
                    $relation->withTrashed()->get()->each(function ($child) {
                        if ($child && in_array(SoftDeletes::class, class_uses_recursive($child))) {
                            $child->restore();
                        }
                    });
                    continue;
                }

                // belongsToMany -> limpiar deleted_at en la pivot si existe
                if ($relation instanceof BelongsToMany) {
                    $pivotTable = $relation->getTable();
                    $foreignKey = $relation->getForeignPivotKeyName();

                    if (Schema::hasTable($pivotTable) && Schema::hasColumn($pivotTable, 'deleted_at')) {
                        DB::table($pivotTable)
                            ->where($foreignKey, $model->getKey())
                            ->update(['deleted_at' => null]);
                    }
                }
            }
        });
    }
}
