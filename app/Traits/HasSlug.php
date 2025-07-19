<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->generateSlug();
        });

        static::updating(function ($model) {
            $model->regenerateSlug();
        });
    }

    /**
     * Generate a unique slug for the model.
     *
     * @return void
     */
    public function generateSlug()
    {
        $slug = Str::slug($this->getAttribute($this->getSlugSourceColumn()), $this->getSlugSeparator());
        $originalSlug = $slug;
        $count = 2;

        // Check if the slug already exists
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . $this->getSlugSeparator() . $count;
            $count++;
        }

        $this->setAttribute('slug', $slug);
    }

    /**
     * Regenerate the slug for the model.
     *
     * @return void
     */
    public function regenerateSlug()
    {
        $slug = Str::slug($this->getAttribute($this->getSlugSourceColumn()), $this->getSlugSeparator());
        $originalSlug = $slug;
        $count = 2;

        // Check if the slug already exists
        while ($this->slugExists($slug, $this->getKey())) {
            $slug = $originalSlug . $this->getSlugSeparator() . $count;
            $count++;
        }

        $this->setAttribute('slug', $slug);
    }

    /**
     * Check if the slug already exists.
     *
     * @param string $slug
     * @param mixed $excludeId
     * @return bool
     */
    protected function slugExists($slug, $excludeId = null)
    {
        $query = static::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    // protected function slugExists($slug, $excludeId = null)
    // {
    //     $query = static::where('slug', $slug);

    //     if ($excludeId !== null) {
    //         $query->where('id', '!=', $excludeId);
    //     }

    //     return $query->exists();
    // }

    /**
     * Get the column name used for generating the slug.
     *
     * @return string
     */
    protected function getSlugSourceColumn()
    {
        return $this->slugSource ?? 'name';
    }

    /**
     * Get the slug separator.
     *
     * @return string
     */
    protected function getSlugSeparator()
    {
        return $this->slugSeparator ?? '-';
    }
}
