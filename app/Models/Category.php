<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon', 'status', 'sort_order'];

    /**
     * Subcategorías directas (nivel 1).
     */
    public function subCategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    /**
     * Productos asociados directamente a esta categoría (category_id).
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Subcategorías activas ordenadas por nombre (alias limpio).
     */
    public function activeSubCategories()
    {
        return $this->hasMany(Subcategory::class)
                    ->where('status', 1)
                    ->orderBy('name');
    }

    /**
     * Scope: solo categorías activas.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Scope optimizado para el mega menú del nav.
     * Carga eager: subcategorías → child_categories.
     * Límite: 6 categorías, 5 subcategorías por categoría, 6 items por subcategoría.
     */
    public function scopeForMenu(Builder $query): Builder
    {
        return $query->where('status', 1)
                     ->with([
                         'subCategories' => function ($q) {
                             $q->where('status', 1)
                               ->orderBy('name')
                               ->with([
                                   'childCategories' => function ($q2) {
                                       $q2->where('status', 1)
                                          ->orderBy('name');
                                   },
                               ]);
                         },
                     ])
                     ->orderBy('sort_order')
                     ->orderBy('name')
                     ->limit(6);
    }
}
