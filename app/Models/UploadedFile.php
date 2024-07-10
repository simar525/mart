<?php

namespace App\Models;

use App\Traits\InteractWithFileStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    use HasFactory, InteractWithFileStorage;

    public function scopeExpired($query)
    {
        $query->where('expiry_at', '<', Carbon::now());
    }

    public function scopeNotExpired($query)
    {
        $query->where('expiry_at', '>', Carbon::now());
    }

    public function isImage()
    {
        return $this->isMimeTypeImage($this->mime_type);
    }

    protected $fillable = [
        'author_id',
        'name',
        'mime_type',
        'extension',
        'size',
        'path',
        'expiry_at',
    ];

    protected $casts = [
        'expiry_at' => 'datetime',
    ];

    public function getShortName()
    {
        $name = $this->name;
        if (strlen($name) > 40) {
            return substr($name, 0, 20) . ".." . substr($name, -4);
        }
        return $name;
    }

    public function getSize()
    {
        return formatBytes($this->size);
    }

    public function getFileLink()
    {
        return getLinkFromStorageProvider($this->path);
    }

    public function deleteFile()
    {
        $storageProvider = storageProvider();
        $processor = new $storageProvider->processor;
        $processor->delete($this->path);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
