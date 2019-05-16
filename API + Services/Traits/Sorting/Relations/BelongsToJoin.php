<?php

namespace App\Traits\Sorting\Relations;

use App\Traits\Sorting\Traits\JoinRelationTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToJoin extends BelongsTo
{
    use JoinRelationTrait;
}
