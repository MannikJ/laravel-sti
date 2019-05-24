<?php

namespace MannikJ\Laravel\SingleTableInheritance;

use Illuminate\Database\Schema\Blueprint;

class SingleTableInheritance
{
    public function column(Blueprint $table, $name = null)
    {
        $name = $name ?: config('single-table-inheritance.default_type_column');
        return $table->string($name);
    }
}
