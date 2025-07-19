<?php

namespace App\Models;

use App\Models\Author;
use App\Traits\HasUuid;
use App\Models\ApplicationTimeline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory, HasUuid;
    protected $guarded = ['id'];

    protected $casts = [
        'article_type' => 'array',
        'co_author_data' => 'array',
        'data' => 'array',
        'article_check' => 'array',
    ];

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id', 'id');
    }

    // public function getFileSizeMbAttribute()
    // {
    //     return round($this->file_size / (1024 * 1024), 2) . ' MB';
    // }

    public function timelines()
    {
        return $this->hasMany(ApplicationTimeline::class, 'application_id', 'id')->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'application_id', 'id');
    }

    public function isReadyToSubmit()
    {
        $requiredKeys = collect(["First Page", "Cover Letter", "Upload Manuscript", "Upload ICMJE Declaration"]);

        $attachmentKeys = $this->attachments()->pluck('attachment_type')->toArray();

        return $requiredKeys->diff($attachmentKeys)->isEmpty();
    }

    public function assignee()
    {
        return $this->belongsTo(Admin::class, 'assigned_to_id', 'id');
    }
}
