<?php

namespace App\Modules\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Copy extends Model
{
    protected $fillable = ['ncid', 'print_batch_id', 'lifecycle_state', 'current_school_id', 'condition'];

    /** Legal transitions per FRS §5.2 */
    public const TRANSITIONS = [
        'PRINTED' => ['IN_WAREHOUSE'],
        'IN_WAREHOUSE' => ['IN_TRANSIT', 'RETIRED'],
        'IN_TRANSIT' => ['AT_SCHOOL', 'LOST'],
        'AT_SCHOOL' => ['ASSIGNED', 'UNDER_REPAIR', 'LOST', 'RETIRED'],
        'ASSIGNED' => ['AT_SCHOOL', 'UNDER_REPAIR', 'LOST'],
        'UNDER_REPAIR' => ['AT_SCHOOL'],
        'LOST' => ['AT_SCHOOL'],
        'RETIRED' => ['DISPOSED'],
        'DISPOSED' => [],
    ];

    public function batch()
    {
        return $this->belongsTo(PrintBatch::class, 'print_batch_id');
    }

    public function canTransition(string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$this->lifecycle_state] ?? []);
    }
}
