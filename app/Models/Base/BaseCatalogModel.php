<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCatalogAttributes;

abstract class BaseCatalogModel extends Model
{
    use HasCatalogAttributes;

    public $timestamps = false;
}
