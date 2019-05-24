<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;

class Vehicle extends Model
{
    use SingleTableInheritance;
}
