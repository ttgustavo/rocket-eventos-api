<?php

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $event_id
 * @property int $status
 * @property \Illuminate\Support\Carbon $checkin_at
 * @property \Illuminate\Support\Carbon $subscribed_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Event|null $event
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereEventId(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereUserId(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendee whereStatus()
 * @method \Illuminate\Database\Eloquent\Builder|Attendee whereEventId(int $id)
 * @method \Illuminate\Database\Eloquent\Builder|Attendee whereUserId(int $id)
 * @method \Illuminate\Database\Eloquent\Builder|Attendee whereStatus()
 */
class Attendee extends Model
{
    use HasFactory;

    const CREATED_AT = 'subscribed_at';

    public $timestamps = true;

    protected $fillable = [
        'status',
        'checkin_at'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
