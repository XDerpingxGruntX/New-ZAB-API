<?php

namespace App\Models;

use App\Enums\ControllerPosition;
use App\Enums\FeedbackRating;
use Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    /** @use HasFactory<FeedbackFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'critic_id',
        'controller_id',
        'position',
        'rating',
        'comment',
        'ip_address',
        'anonymous',
        'approved_at',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['critic', 'controller'];

    public function controller(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function critic(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => ControllerPosition::class,
            'rating' => FeedbackRating::class,
            'anonymous' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }
}
