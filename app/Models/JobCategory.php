<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function workerJobs()
    {
        return $this->hasMany(WorkerJob::class, 'job_category_id');
    }
}
