<?php

namespace App\Models;

use App\Enums\Airport;
use App\Enums\ControllerPosition;
use Database\Factories\EventPositionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Staudenmeir\EloquentJsonRelations\Relations\HasManyJson;

class EventPosition extends Model
{
    /** @use HasFactory<EventPositionFactory> */
    use HasFactory, HasJsonRelationships, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'callsign',
        'airport',
        'position',
        'assigned',
        'user_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedByRegistrations(): HasManyJson
    {
        return $this->hasManyJson(EventRegistration::class, 'requested_positions');
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
            'airport' => Airport::class,
            'position' => ControllerPosition::class,
            'assigned' => 'boolean',
        ];
    }
}
