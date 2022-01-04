<?php

namespace App;

use Carbon;
use Storage;
use Str;
use Illuminate\Http\UploadedFile;
use Jenssegers\Mongodb\Eloquent\Model;

class Coupon extends Model
{
    protected $collection = 'coupons';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'discount_value_formatted',
    ];

    public function followout()
    {
        return $this->hasOne('App\Followout', 'coupon_id');
    }

    public function followout_coupons()
    {
        return $this->hasMany('App\FollowoutCoupon', 'coupon_id');
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id');
    }

    public function picture()
    {
        return $this->hasOne('App\File', 'coupon_id')->where('type', 'coupon');
    }

    public function qr_code()
    {
        return $this->hasOne('App\File', 'coupon_id')->where('type', 'coupon_qr_code');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>=', Carbon::now());
    }

    public function hasPicture()
    {
        return $this->picture !== null;
    }

    public function hasQRCode()
    {
        return $this->qr_code !== null;
    }

    public function defaultPictureURL()
    {
        return url('/img/coupon-pic-default.png');
    }

    public function pictureURL()
    {
        if (!$this->hasPicture()) {
            return $this->defaultPictureURL();
        }

        $picture = $this->picture;

        return Storage::url($picture->path);
    }

    public function qrCodeURL()
    {
        if (!$this->hasQRCode()) {
            return null;
        }

        $picture = $this->qr_code;

        return Storage::url($picture->path);
    }

    public function savePicture(UploadedFile $file)
    {
        if ($this->hasPicture()) {
            $this->deletePicture();
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $picture = File::makeCouponFlyer($file);

        $path = 'coupons/' . $this->id . '/' . $randomString.'.'.$extension;

        Storage::put($path, (string) $picture, 'public');

        $picture = new File([ 'type' => 'coupon', 'path' => $path ]);

        $this->picture()->save($picture);

        return true;
    }

    public function saveQRCode(UploadedFile $file)
    {
        if ($this->hasQRCode()) {
            $this->deleteQRCode();
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $picture = File::makeCouponQRCode($file);

        $path = 'coupons/qr/' . $this->id . '/' . $randomString.'.'.$extension;

        Storage::put($path, (string) $picture, 'public');

        $picture = new File([ 'type' => 'coupon_qr_code', 'path' => $path ]);

        $this->picture()->save($picture);

        return true;
    }

    public function deletePicture()
    {
        if (!$this->hasPicture()) {
            return true;
        }

        $picture = $this->picture;

        Storage::delete($picture->path);

        $picture->delete();

        return true;
    }

    public function deleteQRCode()
    {
        if (!$this->hasQRCode()) {
            return true;
        }

        $picture = $this->qr_code;

        Storage::delete($picture->path);

        $picture->delete();

        return true;
    }

    public function useCount()
    {
        $count = 0;

        foreach ($this->followout_coupons as $coupon) {
            $count += $coupon->useCount();
        }

        return $count;
    }

    public function deleteCoupon()
    {
        foreach ($this->followout_coupons as $coupon) {
            $coupon->deleteCoupon();
        }

        if ($this->followouts) {
            $this->followouts->deleteFollowout();
        }

        $this->deletePicture();
        $this->deleteQRCode();
        $this->delete();

        return true;
    }

    /**
     * Returns true if the coupon is a legacy one and doesn't support creating GEO Coupon Followouts.
     */
    public function legacy()
    {
        return !$this->expires_at;
    }

    public function getDiscountValueFormattedAttribute()
    {
        if ($this->discount_type == 2) {
            return 'Offer';
        }

        if (!$this->discount) {
            return 'No information';
        }

        $result = $this->discount_type == 1 ? '$' : '';
        $result .= $this->discount;
        $result .= $this->discount_type == 0 ? '%' : '';
        $result .= ' off';

        return $result;
    }
}
