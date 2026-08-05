<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    protected $fillable=['title','content','source','embedding','active'];
    protected $casts=['embedding'=>'array','active'=>'boolean'];
}
