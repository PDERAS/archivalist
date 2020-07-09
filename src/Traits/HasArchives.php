<?php

namespace PDERAS\Archivalist\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use PDERAS\Archivalist\Observers\ArchiveObserver;

trait HasArchives {

    /**
     * polymorphic one to many on the archives table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function archives(): MorphMany
    {
        return $this->morphMany(config('archivalist.archive_class'), config('archivalist.morph_name'));
    }

    /**
     * Setup observers
     *
     * @return void
     */
    public static function bootHasArchives(): void
    {
        $model = self::class;
        $model::observe(ArchiveObserver::class);
    }

    /**
     * Get additional/forced columns to always be archived.
     * By default, only 'updated_at' is forced since its never
     * included in the default 'dirty' data, since the user
     * never explicitly sets it.
     *
     * @return array
     */
    public function getArchived(): array
    {
        if (method_exists($this, 'archived')) {
            return $this->archived();
        }

        if (property_exists($this, 'archived')) {
            return collect($this->archived)->mapWithKeys(function ($key) {
                return [$key => $this->{$key}];
            })->jsonSerialize();
        }

        // by default, always save 'updated_at'
        return [ $this->getUpdatedAtColumn() ];
    }

    /**
     * Gets the original (current) values of all 'dirty'
     * columns
     *
     * @return array
     */
    public function getOriginalDirty(): array
    {
        return collect($this->getDirty())
            ->keys()
            ->mapWithKeys(function ($key) {
                return [$key => $this->getRawOriginal($key)];
            })->toArray();
    }

    /**
     * Saves all current dirty data to the archives table
     *
     * @return $this
     */
    public function saveArchive(): self
    {
           // Get the original data for the dirty columns
           $dirty = $this->getOriginalDirty();

           // get any extra columns to be saved
           $extra = $this->getArchived();

           // merge the data
           $data = json_encode(array_merge($dirty, $extra));
           $class = config('archivalist.archive_class');

           $this->archives()->save(
               tap($class::make())->forceFill(['data' => $data])
           );

           return $this;
    }

    /**
     * Gets the full archived history of the current model
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHistory(): Collection
    {
        return $this->archives()
            ->orderBy('id', 'desc')
            ->get()
            ->map
            ->rehydrate()
            ->prepend($this) // its reversed, so the 'current' state goes first
            ->reverse()
            ->values();
    }
}
