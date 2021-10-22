<?php

namespace Pderas\Archivalist\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Pderas\Archivalist\Traits\HasArchives;

class Post extends Model
{
    use HasArchives;

    protected $hidden = ['secret'];
}
