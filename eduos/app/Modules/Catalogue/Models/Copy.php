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

    /**
     * Advance up to $n copies of a title from one lifecycle state to another
     * (FIFO), optionally binding/filtering by school. Returns copies moved.
     * This is what ties the per-copy passports to the operational flows.
     */
    public static function advance(int $titleId, string $from, string $to, int $n, ?int $schoolId = null): int
    {
        $batchIds = PrintBatch::where('textbook_title_id', $titleId)->pluck('id');
        $q = static::whereIn('print_batch_id', $batchIds)->where('lifecycle_state', $from);
        if ($from === 'AT_SCHOOL' && $schoolId) {
            $q->where('current_school_id', $schoolId);
        }
        $ids = $q->orderBy('id')->limit($n)->pluck('id');
        if ($ids->isEmpty()) {
            return 0;
        }
        $update = ['lifecycle_state' => $to];
        if ($to === 'AT_SCHOOL' && $schoolId) {
            $update['current_school_id'] = $schoolId;
        }
        if ($to === 'IN_WAREHOUSE') {
            $update['current_school_id'] = null;
        }
        static::whereIn('id', $ids)->update($update);

        return $ids->count();
    }
}
