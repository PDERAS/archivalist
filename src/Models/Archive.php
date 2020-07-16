<?php

namespace PDERAS\Archivalist\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PDERAS\Archivalist\Traits\IsPolymorphic;

class Archive extends Model {

    use IsPolymorphic;

    /**
     * Get the archived data.
     * - Retrieve the raw json string using `->getOriginal('data')`
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
     * Gets the original unmodified archived data
     *
     * @return array
     */
    public function getArchivedData($asArray = true): array
    {
        $datum = $this->getOriginal('data') ;
        return $asArray ? (array) $datum : $datum;
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
            $this->getArchivedData(true)
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
