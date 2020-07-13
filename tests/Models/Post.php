<?php

namespace PDERAS\Archivalist\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use PDERAS\Archivalist\Traits\HasArchives;

class Post extends Model {
    use HasArchives;

    protected $hidden = [ 'secret' ];
}
