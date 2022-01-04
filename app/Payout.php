<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Payout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipient',
        'amount',
        'fee',
        'batch_id',
        'batch_status',
        'item_id',
        'item_status',
        'transaction_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function getUnresolved()
    {
        $payoutIds = [];

        foreach (self::sent()->whereNull('item_status')->get()->pluck('_id')->toArray() as $id) {
            array_push($payoutIds, $id);
        }

        foreach (self::sent()->whereIn('item_status', ['NEW', 'ONHOLD', 'PENDING', 'UNCLAIMED'])->get()->pluck('_id')->toArray() as $id) {
            array_push($payoutIds, $id);
        }

        foreach (self::sent()->whereIn('batch_status', ['NEW', 'ACKNOWLEDGED', 'PENDING', 'PROCESSING'])->get()->pluck('_id')->toArray() as $id) {
            array_push($payoutIds, $id);
        }

        $payoutIds = array_unique($payoutIds);

        $payouts = self::whereIn('_id', $payoutIds)->get();

        return $payouts;
    }

    public function scopePending($query)
    {
        return $query->whereIn('batch_status', ['NEW', 'ACKNOWLEDGED', 'PENDING', 'PROCESSING']);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('batch_status', ['SUCCESS', 'CANCELED', 'DENIED']);
    }

    public function scopeSent($query)
    {
        return $query->whereNotNull('batch_id')->whereNotNull('batch_status');
    }

    public function isPending()
    {
        return in_array($this->batch_status, ['NEW', 'ACKNOWLEDGED', 'PENDING', 'PROCESSING']);
    }

    public function isCompleted()
    {
        return in_array($this->batch_status, ['SUCCESS', 'CANCELED', 'DENIED']);
    }

    public function isSent()
    {
        return $this->batch_id !== null && $this->batch_status !== null;
    }

    public function isSuccessful()
    {
        return $this->batch_status === 'SUCCESS';
    }

    public function isFailed()
    {
        return in_array($this->batch_status, ['CANCELED', 'DENIED']);
    }

    public function getFormattedRecipient()
    {
        return $this->user ? $this->user->name.' ('.$this->user->email.')' : $this->recipient;
    }

    public function getFormattedItemType()
    {
        $itemTypes = [
            'followee_services' => 'Followee Services',
            'other' => 'Other',
        ];

        return $itemTypes[$this->item_type];
    }

    public function getFormattedAmount()
    {
        return '$ '.$this->amount;
    }

    public function getFormattedAmountWithFees()
    {
        if ($this->fees) {
            return '$ '.$this->amount.($this->fees ? ' + $ '.$this->fees. ' fee' : '');
        }

        return $this->getFormattedAmount();
    }

    public function getPayoutData()
    {
        $apiContext = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret')));
        $apiContext->setConfig(config('paypal.settings'));

        try {
            $result = \PayPal\Api\Payout::get($this->batch_id, $apiContext);
        } catch (Exception $e) {
            return false;
        }

        return json_decode($result, true);
    }

    public function updatePayoutData()
    {
        $apiContext = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret')));
        $apiContext->setConfig(config('paypal.settings'));

        try {
            $result = \PayPal\Api\Payout::get($this->batch_id, $apiContext);
            $item = $result->getItems()[0];

            $this->batch_status = $result->getBatchHeader()->getBatchStatus();
            $this->fees = $result->getBatchHeader()->getFees()->getValue();
            $this->item_id = $item->getPayoutItemId();
            $this->item_status = $item->getTransactionStatus();
            $this->transaction_id = $item->getTransactionId();
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }

        return true;
    }

    public function getAmountAttribute($value)
    {
        return $value ? number_format($value, 2, '.', '') : null;
    }

    public function getFeesAttribute($value)
    {
        return $value ? number_format($value, 2, '.', '') : null;
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value ? number_format($value, 2, '.', '') : null;
    }

    public function setFeesAttribute($value)
    {
        $this->attributes['fees'] = $value ? number_format($value, 2, '.', '') : null;
    }
}
