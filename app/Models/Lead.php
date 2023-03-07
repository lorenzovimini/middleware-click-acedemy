<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Lead
 *
 * @property int $id
 * @property string|null $source
 * @property string|null $referer
 * @property string|null $type
 * @property string $name
 * @property string $surname100
 * @property string|null $region
 * @property string|null $state
 * @property string|null $country
 * @property string|null $phone
 * @property string $email
 * @property string|null $course
 * @property string|null $accept970_at
 * @property string|null $make_processed_at
 * @property string|null $crm_processed_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereAccept970At($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereCrmProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereMakeProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereReferer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereSurname100($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lead whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_ip',
        'referer',
        'email',
        'source',
        'name',
        'surname',
        'region',
        'city',
        'province',
        'country',
        'phone',
        'email',
        'course',
        'accept970_at',
        'make_processed_at',
        'crm_processed_at',
        'request_webhook',
        'response_crm',
        'response_make'
    ];
}
