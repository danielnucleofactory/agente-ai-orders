<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ShippingDocumentComment extends Model implements HasMedia {
    use InteractsWithMedia;

    protected $fillable = [
        'shipping_document_id',
        'user_id',
        'comment',
        'stage'
    ];

    public function shippingDocument() {
        return $this->belongsTo(ShippingDocument::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->format('d/m/Y H:i');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('comment_attachments');
    }
}
