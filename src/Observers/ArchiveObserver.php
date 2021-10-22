<?php

namespace Pderas\Archivalist\Observers;

use Illuminate\Database\Eloquent\Model;

class ArchiveObserver
{
    /**
     * Handle the Models "updating" event.
     * This will save all current 'dirty' data
     * the the archives table.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function updating(Model $model): void
    {
        $model->saveArchive();
    }
}
