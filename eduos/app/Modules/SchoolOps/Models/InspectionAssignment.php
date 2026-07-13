<?php

namespace App\Modules\SchoolOps\Models;

use App\Models\User;
use App\Modules\Registry\Models\School;
use Illuminate\Database\Eloquent\Model;

/** VER-01: verification queue entry — a school assigned to an inspector with a due date. */
class InspectionAssignment extends Model
{
    protected $fillable = ['school_id', 'inspector_id', 'due_on', 'status', 'assigned_by'];

    protected $casts = ['due_on' => 'date'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
