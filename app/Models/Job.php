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
        'status'
    ];

    protected $casts = [
        'open_date'  => 'date:Y-m-d',
        'close_date' => 'date:Y-m-d',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function getStatusAttribute($value): string
    {
        return $this->close_date->endOfDay()->isFuture() ? $value : ($value === 'decision made' ? 'decision made' : 'in review');
    }
}
