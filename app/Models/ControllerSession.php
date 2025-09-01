<?php

namespace App\Models;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use App\Enums\ControllerRating;
use Carbon\CarbonInterval;
use Database\Factories\ControllerSessionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ControllerSession extends Model
{
    /** @use HasFactory<ControllerSessionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'cid',
        'rating',
        'callsign',
        'airport',
        'position',
        'frequency',
        'atis',
        'connected_at',
        'disconnected_at',
        'last_fetched_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'duration',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the duration of the session in minutes.
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn (): CarbonInterval => $this->connected_at->diff($this->disconnected_at),
        );
    }

    /**
     * Automatically set airport and position when callsign is set.
     */
    protected function callsign(): Attribute
    {
        return Attribute::make(
            set: function (string $value): array {
                $parts = explode('_', preg_replace('/_[A-Z0-9]{1,3}_/', '_', $value));

                return [
                    'callsign' => $value,
                    'airport' => $parts[0],
                    'position' => $parts[1] ?? null,
                ];
            },
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => ControllerRating::class,
            'airport' => Airport::class,
            'position' => ControllerPosition::class,
            'connected_at' => 'datetime',
            'disconnected_at' => 'datetime',
            'last_fetched_at' => 'datetime',
        ];
    }
}
