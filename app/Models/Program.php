<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'level',
        'open_date',
        'close_date',
        'description',
        'activity',
        'status',
    ];

    protected $casts = [
        'open_date'  => 'date:Y-m-d',
        'close_date' => 'date:Y-m-d',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d/m/Y');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function getStatusAttribute($value): string
    {
        return $this->close_date->endOfDay()->isFuture() ? $value : ($value === 'decision made' ? 'decision made' : 'in review');
    }


}
