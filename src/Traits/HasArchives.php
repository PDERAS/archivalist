<?php

namespace Pderas\Archivalist\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Pderas\Archivalist\Observers\ArchiveObserver;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

trait HasArchives
{
    /**
     * Support for JSON relationships
     */
    use HasJsonRelationships;

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
        return [$this->getUpdatedAtColumn() => $this->updated_at];
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
                return [$key => $this->getOriginal($key)];
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

        $this->archiveWithData($dirty);

        return $this;
    }

    public function archiveWithData($data)
    {
        $data = collect($data)
            ->filter(function ($value, $key) {
                // Filter out $hidden attributes
                return !in_array($key, $this->getHidden());
            })
            ->toArray();

        if (!$data) {
            // no updateable data...
            return;
        }

        // get any extra columns to be saved
        $extra = $this->getArchived();

        $class = config('archivalist.archive_class');
        // merge the data
        $json = json_encode(array_merge($data, $extra));

        //  Run the optional callback logic
        $this->beforeArchiveCallback();

        $this->archives()->save(
            tap($class::make())->forceFill(['data' => $json])
        );
    }

    /**
     * Gets the full archived history of the current model
     *
     * @return \Illuminate\Support\Collection
     */
    public function getHistory(): Collection
    {
        $archives = $this->archives()
            ->orderBy('id', 'desc')
            ->get();

        $parent = $this;
        $mapped  = collect([$parent]);

        foreach ($archives as $archive) {
            $parent = $archive->rehydrate($parent);
            $mapped->push($parent);
        }

        return $mapped->reverse()->values();
    }


    /**
     * Helper callback, implement beforeArchive method in your models to run any extra
     * logic before archive logic is run.
     */
    public function beforeArchiveCallback()
    {
        if (method_exists($this, 'beforeArchive')) {
            return $this->beforeArchive();
        }
    }
}
