<?php

namespace App\Models;

use Database\Factories\PilotReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PilotReport extends Model
{
    /** @use HasFactory<PilotReportFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'location',
        'aircraft',
        'altitude',
        'sky',
        'turbulence',
        'icing',
        'visibility',
        'temperature',
        'wind',
        'urgent',
        'manual',
        'raw',
        'reported_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urgent' => 'boolean',
            'manual' => 'boolean',
            'reported_at' => 'datetime',
        ];
    }
}
