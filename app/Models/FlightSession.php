<?php

namespace App\Models;

use Database\Factories\FlightSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightSession extends Model
{
    /** @use HasFactory<FlightSessionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'cid',
        'callsign',
        'aircraft',
        'departure_airport',
        'arrival_airport',
        'latitude',
        'longitude',
        'heading',
        'altitude',
        'planned_altitude',
        'speed',
        'route',
        'remarks',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
