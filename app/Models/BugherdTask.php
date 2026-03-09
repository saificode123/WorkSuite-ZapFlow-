<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugherdTask extends Model
{
    protected $fillable = [
        'project_id','task_id', 'bugherd_task_id','status','priority','description','tags','raw','synced_at'
    ];
    protected $casts = [
        'tags' => 'array',
        'raw'  => 'array',
        'synced_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
