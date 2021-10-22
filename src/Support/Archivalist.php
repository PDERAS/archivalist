<?php

namespace Pderas\Archivalist\Support;

use Illuminate\Database\Eloquent\Builder;
use Pderas\Archivalist\Traits\HasIntercepts;

class Archivalist
{
    use HasIntercepts;

    /** @var Illuminate\Database\Eloquent\Builder */
    protected $query;

    /**
     * Proxy to capture any final query calls
     * before they happen, ->update(...) etc...
     */
    public function proxy(Builder $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * `->update(...)` interception method
     * Archives the queried models by checking
     * the data to be used in the update & forcing
     * an archive on the models using those same keys
     *
     * @param array $data
     *
     * @return void
     */
    protected function interceptUpdate(array $data): void
    {
        $this->query->get()->each(function ($model) use ($data) {
            // Update 'data' to be previous (current) state ... pre-update
            foreach (array_keys($data) as $key) {
                $data[$key] = $model->{$key};
            }

            // Archive future data
            $model->archiveWithData($data);
        });
    }
}
