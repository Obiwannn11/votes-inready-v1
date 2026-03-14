<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionScreenshot extends Model
{
    protected $fillable = [
        'submission_id',
        'image_path',
        'caption',
        'display_order',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
