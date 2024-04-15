<?php

namespace App\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $details
 * @property \Illuminate\Support\Carbon $subscription_date_start
 * @property \Illuminate\Support\Carbon $subscription_date_end
 * @property \Illuminate\Support\Carbon $presentation_at
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event wherePresentationAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSubscriptionDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSubscriptionDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 */
class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'details',
        'subscription_date_start',
        'subscription_date_end',
        'presentation_at',
        'status'
    ];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'subscription_date_start' => 'datetime',
            'subscription_date_end' => 'datetime',
            'presentation_at' => 'datetime',
        ];
    }
}
