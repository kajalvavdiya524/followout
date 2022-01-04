<?php

namespace App\Helpers;

use Str;
use Carbon;
use App\User;
use App\PromoCode;
use App\SubscriptionCode;
use MongoDB\BSON\UTCDateTime;

require_once(base_path('vendor/chargebee/chargebee-php/lib/ChargeBee.php'));

\ChargeBee_Environment::configure(env('CHARGEBEE_SITE'), env('CHARGEBEE_KEY'));

class PaymentHelper
{
    public static function parseChargebeeCustomerFromResponse($object)
    {
        return $object->getValues();
    }

    public static function parseChargebeeSubscriptionFromResponse($object)
    {
        return $object->getValues();
    }

    public static function parseChargebeeCreditNoteFromResponse($object)
    {
        return $object->getValues();
    }

    public static function parseChargebeeInvoiceFromResponse($object)
    {
        return $object->getValues();
    }

    public static function getChargebeeCustomer($userId)
    {
        $user = User::findOrFail($userId);

        try {
            $result = \ChargeBee_Customer::retrieve($user->id);
        } catch (\Exception $e) {
            return null;
        }

        return static::parseChargebeeCustomerFromResponse($result->customer());
    }

    public static function getChargebeeSubscription($userId)
    {
        $user = User::find($userId);

        if (is_null($user) || is_null($user->subscription)) {
            return null;
        }

        $subscriptionId = $user->subscription->chargebee_subscription_id;

        try {
            $result = \ChargeBee_Subscription::retrieve($subscriptionId);
        } catch (\Exception $e) {
            return null;
        }

        return static::parseChargebeeSubscriptionFromResponse($result->subscription());
    }

    public static function getChargebeeSubscriptionById($chargebeeSubscriptionId)
    {
        try {
            $result = \ChargeBee_Subscription::retrieve($chargebeeSubscriptionId);
        } catch (\Exception $e) {
            return null;
        }

        return static::parseChargebeeSubscriptionFromResponse($result->subscription());
    }

    public static function getChargebeeInvoice($invoiceId)
    {
        try {
            $result = \ChargeBee_Invoice::retrieve($invoiceId);
        } catch (\Exception $e) {
            return null;
        }

        return static::parseChargebeeInvoiceFromResponse($result->invoice());
    }

    public static function getChargebeeInvoicesForCustomer($customerId)
    {
        $invoices = [];

        $offset = null;

        $query['limit'] = '100';
        $query['status[is]'] = 'paid';
        $query['customerId[is]'] = $customerId;

        try {
            do {
                if ($offset !== null) {
                    $query['offset'] = $offset;
                } else {
                    unset($query['offset']);
                }

                $result = \ChargeBee_Invoice::all($query);

                // Reset index to 0
                $result->rewind();

                $offset = $result->nextOffset();

                if ($result->count() > 0) {
                    for ($i = 0; $i < $result->count(); $i++) {
                        if ($i > 0) {
                            $result->next();
                        }

                        $invoice = static::parseChargebeeInvoiceFromResponse($result->current()->invoice());

                        $invoices[] = $invoice;
                    }
                }
            } while ($offset !== null);
        } catch (\Exception $e) {
            return $invoices;
        }

        return $invoices;
    }

    public static function updateOrCreateSubscription($userId, $subscriptionType)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        if (is_null($user->subscription)) {
            if ($subscriptionType === 'subscription_monthly') {
                $user->subscription()->create([
                    'type' => $subscriptionType,
                    'expires_at' => new UTCDateTime(Carbon::now()->addMonth()->timestamp * 1000)
                ]);
            } elseif ($subscriptionType === 'subscription_yearly') {
                $user->subscription()->create([
                    'type' => $subscriptionType,
                    'expires_at' => new UTCDateTime(Carbon::now()->addYear()->timestamp * 1000)
                ]);
            } else {
                $user->subscription()->create([
                    'type' => $subscriptionType,
                    'expires_at' => new UTCDateTime(Carbon::now()->addMonth()->timestamp * 1000)
                ]);
            }
        } else {
            $data['type'] = $subscriptionType;

            if ($subscriptionType === 'subscription_monthly') {
                $data['expires_at'] = new UTCDateTime($user->subscription->expires_at->addMonth()->timestamp * 1000);
            } elseif ($subscriptionType === 'subscription_yearly') {
                $data['expires_at'] = new UTCDateTime($user->subscription->expires_at->addYear()->timestamp * 1000);
            }

            $user->subscription()->update($data);
        }

        if ($user->isFollowhost()) {
            FollowoutHelper::updateOrCreateDefaultFollowout($user->id);
        }

        return true;
    }

    public static function updateOrCreateOneOffChargebeeSubscription($userId, $chargebeeSubscription, SubscriptionCode $subscriptionCode = null)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $subscription = [
            'type' => $chargebeeSubscription['plan_id'] === 'followouts-pro-yearly' ? 'subscription_yearly' : 'subscription_monthly',
            'expires_at' => new UTCDateTime(((int) $chargebeeSubscription['current_term_end']) * 1000),
            'next_billing_at' => null,
            'is_canceled' => true,
            'is_resumable' => false,
            'chargebee_plan_id' => $chargebeeSubscription['plan_id'],
            'chargebee_subscription_id' => $chargebeeSubscription['id'],
            'contractTerm' => [
                'actionAtTermEnd' => 'cancel',
            ],
        ];

        if ($subscriptionCode) {
            $subscription['subscription_code_id'] = $subscriptionCode->id;
        }

        if (is_null($user->subscription)) {
            $user->subscription()->create($subscription);
        } else if ($user->subscription->isBasic()) {
            $user->subscription()->delete();

            $user->subscription()->create($subscription);
        } else {
            $user->subscription()->update($subscription);
        }

        if ($user->isFollowhost()) {
            FollowoutHelper::updateOrCreateDefaultFollowout($user->id);
        }

        return true;
    }

    public static function updateOrCreateChargebeeSubscription($userId, $chargebeeSubscription)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        // 'expires_at' will be set 10 minutes later so that we'd have some time to bill the user

        $subscription = [
            'type' => $chargebeeSubscription['plan_id'] === 'followouts-pro-yearly' ? 'subscription_yearly' : 'subscription_monthly',
            'expires_at' => new UTCDateTime(((int) $chargebeeSubscription['current_term_end'] + 600) * 1000),
            'next_billing_at' => new UTCDateTime((int) $chargebeeSubscription['next_billing_at'] * 1000),
            'chargebee_plan_id' => $chargebeeSubscription['plan_id'],
            'chargebee_subscription_id' => $chargebeeSubscription['id'],
            'is_resumable' => true,
        ];

        if (is_null($user->subscription)) {
            $user->subscription()->create($subscription);
        } else if ($user->subscription->isBasic()) {
            $user->subscription()->delete();

            $user->subscription()->create($subscription);
        } else {
            $user->subscription()->update($subscription);
        }

        if ($user->isFollowhost()) {
            FollowoutHelper::updateOrCreateDefaultFollowout($user->id);
        }

        return true;
    }

    public static function deleteChargebeeSubscription($chargebeeSubscriptionId)
    {
        try {
            $result = \ChargeBee_Subscription::delete($chargebeeSubscriptionId);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public static function deleteChargebeeSubscriptionForUser($userId)
    {
        $user = User::find($userId);

        $subscription = static::getChargebeeSubscription($userId);

        if (is_null($user) || is_null($subscription)) {
            return false;
        }

        $result = \ChargeBee_Subscription::delete($user->subscription->chargebee_subscription_id);

        return true;
    }

    public static function deleteChargebeeCustomer($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $chargebeeCustomer = static::getChargebeeCustomer($user->id);

        if (is_null($chargebeeCustomer)) {
            return false;
        }

        try {
            $result = \ChargeBee_Customer::delete($user->id);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public static function startOneOffChargebeeSubscription($chargebeeSubscriptionId)
    {
        try {
            $result = \ChargeBee_Subscription::update($chargebeeSubscriptionId, [
                'start_date' => 0, // start subscription immediately
                'invoiceImmediately' => false, // don't charge the card again, since we've already collected the payment for one period
            ]);

            // Remove credit notes, because ChargeBee incorrectly thinks that we owe customer something
            $creditNotes = $result->creditNotes() ?? [];
            foreach ($creditNotes as $creditNote) {
                $creditNote = static::parseChargebeeCreditNoteFromResponse($creditNote);
                \ChargeBee_CreditNote::delete($creditNote['id']);
            }
        } catch (\Exception $e) {
            return false;
        }

        // This is a one-off subscription so we need to cancel it after it activates to prevent further charges
        static::cancelChargebeeSubscription($chargebeeSubscriptionId);

        return true;
    }

    public static function changeChargebeeSubscriptionToExpired($userId)
    {
        $chargebeeSubscription = static::getChargebeeSubscription($userId);

        if (is_null($chargebeeSubscription)) {
            return false;
        }

        try {
            $result = \ChargeBee_Subscription::changeTermEnd($chargebeeSubscription['id'], ['termEndsAt' => Carbon::now()->addSeconds(15)->timestamp]);
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

    public static function createChargebeeSubscriptionCodeAndStartSubscription($chargebeeSubscriptionId, $email)
    {
        $result = \ChargeBee_Subscription::retrieve($chargebeeSubscriptionId);
        $chargebeeSubscription = static::parseChargebeeSubscriptionFromResponse($result->subscription());

        if (!isset($chargebeeSubscription['id'])) {
            throw new \Exception('Chargebee subscription doesn\'t exist.', 500);
        }

        // Check if subscription code exists for this subscription
        $subscriptionCode = SubscriptionCode::where('subscription_id', $chargebeeSubscriptionId)->first();

        // This is a one-off subscription so we need to cancel it after it activates to prevent further charges
        static::cancelChargebeeSubscription($chargebeeSubscriptionId);

        if (!$subscriptionCode) {
            if ($chargebeeSubscription['plan_id'] === 'followouts-pro-yearly') {
                $shortPlanName = 'annual';
            } elseif ($chargebeeSubscription['plan_id'] === 'followouts-pro-monthly') {
                $shortPlanName = 'monthly';
            } else {
                $shortPlanName = $chargebeeSubscription['plan_id'];
            }

            $subscriptionCode = new SubscriptionCode;
            $subscriptionCode->email = mb_strtolower($email);
            $subscriptionCode->account_activation_token = Str::random(48);
            $subscriptionCode->code = mb_strtoupper($shortPlanName . '-' . Str::random(24));
            $subscriptionCode->chargebee_subscription_id = $chargebeeSubscription['id'];
            $subscriptionCode->activated_at = null;
            $subscriptionCode->expires_at = new UTCDateTime(((int) $chargebeeSubscription['current_term_end']) * 1000);
            $subscriptionCode->save();
        }

        return $subscriptionCode;
    }

    public static function createChargebeeSubscriptionCode($chargebeeSubscriptionId, $email)
    {
        $result = \ChargeBee_Subscription::retrieve($chargebeeSubscriptionId);
        $chargebeeSubscription = static::parseChargebeeSubscriptionFromResponse($result->subscription());

        if (!isset($chargebeeSubscription['id'])) {
            throw new \Exception('Chargebee subscription doesn\'t exist.', 500);
        }

        try {
            // The subscription is in the future, so the customer wasn't billed yet, we'll bill the customer once
            $chargeResult = \ChargeBee_Subscription::chargeFutureRenewals($chargebeeSubscriptionId);
        } catch (\Exception $e) {
            // Subscribe attempt failed, we need to delete the subscription
            PaymentHelper::deleteChargebeeSubscription($chargebeeSubscriptionId);

            throw $e;
        }

        // If invoice is not issued, this means that card is valid but we coudn't charge it
        if (is_null($chargeResult->invoice())) return null;

        // Check if subscription code exists for this subscription
        $subscriptionCode = SubscriptionCode::where('subscription_id', $chargebeeSubscriptionId)->first();

        if (!$subscriptionCode) {
            if ($chargebeeSubscription['plan_id'] === 'followouts-pro-yearly') {
                $shortPlanName = 'annual';
            } elseif ($chargebeeSubscription['plan_id'] === 'followouts-pro-monthly') {
                $shortPlanName = 'monthly';
            } else {
                $shortPlanName = $chargebeeSubscription['plan_id'];
            }

            $subscriptionCode = new SubscriptionCode;
            $subscriptionCode->email = mb_strtolower($email);
            $subscriptionCode->account_activation_token = Str::random(48);
            $subscriptionCode->code = mb_strtoupper($shortPlanName . '-' . Str::random(24));
            $subscriptionCode->chargebee_subscription_id = $chargebeeSubscription['id'];
            $subscriptionCode->activated_at = null;
            $subscriptionCode->save();
        }

        return $subscriptionCode;
    }

    /**
     * Cancel subscription (by putting it on the grace period).
     *
     * @param  string $chargebeeSubscriptionId
     * @return bool
     *
     * @throws \Exception
     */
    public static function cancelChargebeeSubscription($chargebeeSubscriptionId)
    {
        try {
            $result = \ChargeBee_Subscription::cancel($chargebeeSubscriptionId, [
                'endOfTerm' => true,
                'creditOptionForCurrentTermCharges' => 'none',
                'unbilledChargesOption' => 'delete',
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Activate subscription by code.
     *
     * @param  string $codeId
     * @param  string $userId
     * @return bool
     */
    public static function useChargebeeSubscriptionCode($codeId, $userId)
    {
        $code = SubscriptionCode::where('code', $codeId)->unactivated()->first();
        $user = User::find($userId);

        if (is_null($code) || is_null($user)) {
            return false;
        }

        if (is_null($code->expires_at)) {
            $subscriptionStarted = static::startOneOffChargebeeSubscription($code->chargebee_subscription_id);

            if (!$subscriptionStarted) return false;
        } elseif ($code->expires_at < now()) {
            return false;
        }

        $chargebeeSubscription = static::getChargebeeSubscriptionById($code->chargebee_subscription_id);

        static::updateOrCreateOneOffChargebeeSubscription($user->id, $chargebeeSubscription, $code);

        $code->activated_at = now();
        $code->save();

        return $code;
    }

    public static function validatePromoCode($code, $user = null)
    {
        if (is_null($user)) {
            return false;
        }

        $code = PromoCode::where('code', $code)->first();

        if (is_null($code)) {
            return false;
        }

        if ($code->users()->where('_id', $user->id)->exists()) {
            return false;
        }

        return true;
    }
}
