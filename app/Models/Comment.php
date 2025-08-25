<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Comment extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'shipping_document_comments';

    protected $fillable = [
        'comment',
        'user_id',
        'shipping_document_id',
        'stage'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippingDocument()
    {
        return $this->belongsTo(ShippingDocument::class);
    }

    public function attachments()
    {
        return $this->media()->where('collection_name', 'comment_attachments');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('comment_attachments');
    }
}
