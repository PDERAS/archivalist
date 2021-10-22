<?php

namespace Pderas\Archivalist\Traits;

use Illuminate\Database\Eloquent\Model;

trait IsPolymorphic
{
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
}
