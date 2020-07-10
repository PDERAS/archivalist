<?php

namespace PDERAS\Archivalist\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Archive extends Model {
    /**
     * Get the archived data.
     * - Retrieve the raw json string using `->getRawOriginal('data')`
     * - Query json data using `->where('data->id', 1)`
     *
     * @param  string  $value
     *
     * @return object
     */
    public function getDataAttribute(string $value): object
    {
        return json_decode($value);
    }

    /**
     * get raw database columns for the supplied model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function getTableColumns(Model $model): array {
        return Schema::getColumnListing($model->getTable());
    }

    /**
     * Get the related model
     */
    public function getRelatedModel(): Model
    {
        $class = $this->getRelatedClass();
        return $class::find($this->{config('archivalist.morph_name') . '_id'});
    }

    /**
     * get the related models class
     */
    public function getRelatedClass(): string
    {
        return $this->{config('archivalist.morph_name') . '_type'};
    }

    /**
     * get the related models id
     *
     * @return string
     */
    public function getRelatedId(): string
    {
        return $this->{config('archivalist.morph_name') . '_id'};
    }

    public function getCurrentData(): array
    {
        $model = $this->getRelatedModel();
        $tableColumns = $this->getTableColumns($model);
        return collect($model)->only($tableColumns)->toArray();
    }

    public function getArchivedData(): array
    {
        return json_decode($this->getRawOriginal('data'), true);
    }

    /**
     * Rehydrates the 'Archive' model into its original form
     * i.e. if its a User Archive, then it will return a User model
     *
     * @return $model
     */
    public function rehydrate($previous): Model
    {
        // Get the parent class
        $class = $this->getRelatedClass();

        $tableColumns = $this->getTableColumns($previous);

        // collect database columns & update
        $archivedAttributes = array_merge(
            collect($previous)->only($tableColumns)->toArray(),
            $this->getArchivedData()
        );

        // create a new model with the archived attributes
        return tap(new $class, function ($instance) use ($archivedAttributes) {

            // Update All Fields
            $instance->setRawAttributes($archivedAttributes);

            // Update & Format Timestamps
            // \Illuminate\Database\Eloquent\Concerns\HasTimestamps@updateTimestamps
            $updatedAtColumn = $instance->getUpdatedAtColumn();
            if (! is_null($updatedAtColumn)) {
                $instance->setUpdatedAt($archivedAttributes[$updatedAtColumn]);
            }

            $createdAtColumn = $instance->getCreatedAtColumn();
            if (! is_null($createdAtColumn)) {
                $instance->setCreatedAt($archivedAttributes[$createdAtColumn]);
            }
        });
    }
}
