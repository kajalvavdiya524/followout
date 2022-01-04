<?php

namespace App;

use Carbon;
use Storage;
use Jenssegers\Mongodb\Eloquent\Model;

class Video extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'processed_at',
    ];

    public function file()
    {
        return $this->belongsTo('App\File', 'file_id');
    }

    public function uploader()
    {
        return $this->belongsTo('App\User', 'uploader_id');
    }

    public function scopeUnprocessed($query)
    {
        return $query->whereNull('processed_at');
    }

    public function isProcessed()
    {
        return !is_null($this->processed_at);
    }

    public function url($format = 'mp4')
    {
        if (strtolower($format) === 'thumb') {
            return Storage::url($this->path_thumb);
        }

        if (strtolower($format) === 'm3u8') {
            return Storage::url($this->path_m3u8);
        }

        return Storage::url($this->path_mp4);
    }

    public function thumbURL()
    {
        return $this->url('thumb');
    }

    public function markAsProcessed()
    {
        if (Storage::exists($this->path_raw)) {
            Storage::delete($this->path_raw);
        }

        Storage::setVisibility($this->path_thumb, 'public');
        Storage::setVisibility($this->path_mp4, 'public');
        Storage::setVisibility($this->path_m3u8, 'public');
        Storage::setVisibility($this->path_m3u8_hls, 'public');
        Storage::setVisibility($this->path_m3u8_hls_ts, 'public');

        $this->processed_at = Carbon::now();
        $this->path_raw = null;
        $this->save();

        return true;
    }

    public function deleteVideo($force = false)
    {
        if (!$force && !$this->isProcessed()) {
            return false;
        }

        // Raw
        if (Storage::exists($this->path_raw)) {
            Storage::delete($this->path_raw);
        }

        // Thumbnail
        if (Storage::exists($this->path_thumb)) {
            Storage::delete($this->path_thumb);
        }

        // MP4
        if (Storage::exists($this->path_mp4)) {
            Storage::delete($this->path_mp4);
        }

        // HLS
        if (Storage::exists($this->path_m3u8)) {
            Storage::delete($this->path_m3u8);
        }

        if (Storage::exists($this->path_m3u8_hls)) {
            Storage::delete($this->path_m3u8_hls);
        }

        if (Storage::exists($this->path_m3u8_hls_ts)) {
            Storage::delete($this->path_m3u8_hls_ts);
        }

        $this->file()->delete();
        $this->delete();

        return true;
    }

    public function getPathM3u8HlsAttribute()
    {
        $from = $this->basename.'.m3u8';
        $to = $this->basename.'-hls.m3u8';

        return str_replace($from, $to, $this->path_m3u8);
    }

    public function getPathM3u8HlsTsAttribute()
    {
        $from = $this->basename.'.m3u8';
        $to = $this->basename.'-hls.ts';

        return str_replace($from, $to, $this->path_m3u8);
    }
}
