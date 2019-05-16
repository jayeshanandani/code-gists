<?php

namespace App\Traits\Sorting\Relations;

use App\Traits\Sorting\Traits\JoinRelationTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HasOneJoin extends HasOne
{
    use JoinRelationTrait;
}
