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
     * Rehydrates the 'Archive' model into its original form
     * i.e. if its a User Archive, then it will return a User model
     *
     * @return $model
     */
    public function rehydrate(): Model
    {
        // Get the parent class
        $class = $this->{config('archivalist.morph_name') . '_type'};

        // get the actual parent model
        $model = $class::find($this->{config('archivalist.morph_name') . '_id'});

        // get raw database columns
        $tableColumns = Schema::getColumnListing($model->getTable());

        // get the archived data
        $archival = json_decode($this->getRawOriginal('data'), true);

        // collect database columns & update
        $attributes = array_merge(collect($model)->only($tableColumns)->toArray(), $archival);

        // create a new model with the archived attributes
        return tap(new $class, function ($instance) use ($attributes, $class) {

            // Update All Fields
            $instance->setRawAttributes($attributes);

            // Update & Format Timestamps
            // \Illuminate\Database\Eloquent\Concerns\HasTimestamps@updateTimestamps
            $updatedAtColumn = $instance->getUpdatedAtColumn();
            if (! is_null($updatedAtColumn)) {
                $instance->setUpdatedAt($attributes[$updatedAtColumn]);
            }
            $createdAtColumn = $instance->getCreatedAtColumn();
            if (! is_null($createdAtColumn)) {
                $instance->setCreatedAt($attributes[$createdAtColumn]);
            }
        });
    }
}
