<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'careers';

    protected $fillable = [
        'title',
        'description',
        'open_date',
        'close_date',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
