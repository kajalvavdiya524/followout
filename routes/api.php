<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function () {
    // Login
    Route::post('/login', 'API\Auth\LoginController@login');
    Route::post('/login/facebook', 'API\Auth\LoginController@handleFacebookLogin');
    Route::post('/login/anonymous', 'API\Auth\LoginController@loginAnonymous');

    // Register
    Route::post('/register', 'API\Auth\RegisterController@register');

    // Support
    Route::post('/support/contact', 'API\HomeController@contactSupport')->middleware('throttle:10')->name('api.support.contact');

    // Password resets
    Route::post('/password/email', 'API\Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'API\Auth\ResetPasswordController@reset');

    // Search
    Route::post('/search/followouts', 'API\SearchController@followouts');
    Route::post('/search/places', 'API\SearchController@places');
    Route::post('/search/place', 'API\SearchController@place');
    Route::post('/search/users', 'API\SearchController@users');

    // Users
    Route::get('/activate_account/{token}', 'API\UsersController@activateAccount');
    Route::get('/users/{user}', 'API\UsersController@show');
    Route::get('/users/{user}/avatar_url', 'API\UsersController@getAvatarUrl');
    Route::get('/users/{user}/checkins', 'API\UsersController@checkins');
    Route::get('/users/{user}/followees', 'API\UsersController@followees');
    Route::get('/users/{user}/followouts', 'API\UsersController@followouts');
    Route::get('/users/{user}/following', 'API\UsersController@following');
    Route::get('/users/{user}/followers', 'API\UsersController@followers');
    Route::get('/users/{user}/subscribers', 'API\UsersController@subscribers');

    // Followouts
    Route::get('/followouts', 'API\FollowoutsController@index');
    Route::get('/followouts/{followout}', 'API\FollowoutsController@show');
    Route::get('/followouts/{followout}/author', 'API\FollowoutsController@getAuthor');
    Route::get('/followouts/{followout}/checkins', 'API\FollowoutsController@getCheckins');
    Route::get('/followouts/{followout}/favorited', 'API\FollowoutsController@getFavorited');
    Route::get('/followouts/{followout}/followees', 'API\FollowoutsController@getFollowees');
    Route::get('/followouts/{followout}/coupons', 'API\FollowoutsController@coupons');

    // Coupons
    Route::get('/followout_coupons/{coupon}', 'API\CouponsController@show');

    // Followees
    Route::get('/followees/{followee}', 'API\FolloweesController@show');

    // Validation
    Route::post('/validate/users/exists/email', 'API\ValidationController@validateUserExistsByEmail');
    Route::post('/validate/user/{user}/has/social_account', 'API\ValidationController@validateUserHasSocialAccount');
    Route::post('/validate/user/{user}/favorited', 'API\ValidationController@validateUserFavorited');
    Route::post('/validate/user/follows', 'API\ValidationController@validateUserFollows');

    // Collections
    Route::get('/collections/countries', 'API\CollectionsController@countries');
    Route::get('/collections/experience_categories', 'API\CollectionsController@experienceCategories');
    Route::get('/collections/products', 'API\CollectionsController@products');
    Route::get('/collections/google_places_types', 'API\CollectionsController@googlePlacesTypes');
    Route::get('/collections/static_content', 'API\CollectionsController@staticContent');
});

Route::prefix('v1')->middleware(['auth:api'])->group(function () {
    Route::get('/me', 'API\UsersController@me');

    // Feed
    Route::get('/feed', 'API\FollowoutsController@feed');

    // Users
    Route::get('/users', 'API\UsersController@getUsers');

    // Followouts
    Route::get('/checkin/{followout}', 'API\FollowoutsController@checkin');

    // Followees
    Route::get('/nearby_followees/{latlng?}', 'API\UsersController@getClosestFollowees');
});

Route::prefix('v1')->middleware(['auth:api', 'identified:api'])->group(function () {
    // Register
    Route::post('/register/social', 'API\Auth\RegisterController@registerFromSocial');

    // Account activation
    Route::get('/account_activation/resend', 'API\UsersController@resendAccountActivationEmail');

    // Notifications
    Route::get('/notifications', 'API\NotificationsController@index')->name('api.notifications.index');
    Route::delete('/notifications', 'API\NotificationsController@destroyAll')->name('api.notifications.destroy.all');
    Route::get('/notifications/{notification}/read', 'API\NotificationsController@read')->name('api.notifications.read');
    Route::get('/notifications/mark-all-as-read', 'API\NotificationsController@readAll')->name('api.notifications.read-all');
    Route::delete('/notifications/{notification}', 'API\NotificationsController@destroy')->name('api.notifications.destroy');
    Route::delete('/notifications', 'API\NotificationsController@destroyAll')->name('api.notifications.destroy.all');

    // Search
    Route::post('/search/promo_code', 'API\SearchController@promoCode');

    // Users
    Route::patch('/users/{user}', 'API\UsersController@update');
    Route::patch('/users/{user}/device', 'API\UsersController@updateDevice');
    Route::patch('/users/{user}/business_type', 'API\UsersController@updateFollowhostGoogleBusinessType');
    Route::get('/users/{user}/subscribe', 'API\UsersController@subscribe')->name('api.users.subscribe');
    Route::get('/users/{user}/unsubscribe', 'API\UsersController@unsubscribe')->name('api.users.unsubscribe');
    Route::post('/deactivate_account', 'API\UsersController@requestAccountDeletion');
    Route::post('/users/{user}/block', 'API\UsersController@block');
    Route::post('/users/{user}/unblock', 'API\UsersController@unblock');
    Route::delete('/users/{user}', 'API\AdminController@deleteUser')->name('api.users.destroy');

    // Messages
    Route::get('/messages', 'API\MessagesController@chats')->name('api.messages.chats');
    Route::get('/messages/{user}', 'API\MessagesController@chat')->name('api.messages.chat');
    Route::post('/messages/{user}', 'API\MessagesController@send')->name('api.messages.send');

    // Settings
    Route::post('/settings/password', 'API\SettingsController@changePassword');
    Route::post('/settings/social_accounts/disconnect', 'API\SettingsController@changePassword');
    Route::get('/settings/notifications', 'API\SettingsController@notifications');
    Route::post('/settings/notifications', 'API\SettingsController@updateNotificationSettings');

    // Followouts
    Route::post('/followout_quick_create', 'API\FollowoutsController@create');
    Route::post('/followouts', 'API\FollowoutsController@store');
    Route::patch('/followouts/{followout}', 'API\FollowoutsController@update');
    Route::delete('/followouts/{followout}', 'API\FollowoutsController@destroy');
    Route::post('/followouts/{followout}/invite', 'API\FollowoutsController@inviteAttendees');
    Route::post('/followouts/{followout}/coupons/attach/{coupon}', 'API\FollowoutsController@linkCoupon')->name('api.followouts.coupons.use');
    Route::post('/followouts/{followout}/coupons/detach/{coupon}', 'API\FollowoutsController@disableCoupon')->name('api.followouts.coupons.disable');

    // Coupons
    Route::get('/coupons', 'API\CouponsController@index');
    Route::post('/coupons', 'API\CouponsController@store');
    Route::get('/coupons/{coupon}/create_followout', 'API\CouponsController@createFollowout');
    Route::get('/followout_coupons/{coupon}/use', 'API\CouponsController@useCoupon');

    // Followees
    Route::post('/followees/invite', 'API\FolloweesController@inviteFollowee')->name('api.followouts.invite');
    Route::post('/followees/present_request', 'API\FolloweesController@presentFollowoutRequest')->name('api.followouts.present-request');
    Route::get('/followees/present_request/{followout}/{user}/accept', 'API\FolloweesController@acceptPresentFollowoutRequest')->name('api.followouts.present-request.accept');
    Route::get('/followees/present_request/{followout}/{user}/decline', 'API\FolloweesController@declinePresentFollowoutRequest')->name('api.followouts.present-request.decline');
    Route::get('/followees/followee_intro/{user}', 'API\FolloweesController@sendFolloweeIntro')->name('api.users.followee-intro');
    Route::get('/followees/invitation/{followout}/accept', 'API\FolloweesController@acceptFolloweeInvitation');
    Route::get('/followees/invitation/{followout}/decline', 'API\FolloweesController@declineFolloweeInvitation');

    // Favorites
    Route::get('/favorites', 'API\FavoritesController@index');
    Route::get('/favorites/add/{modelName}/{modelId}', 'API\FavoritesController@favorite');
    Route::get('/favorites/remove/{modelName}/{modelId}', 'API\FavoritesController@unfavorite');

    // Validation
    Route::post('/validate/promo_code', 'API\ValidationController@validatePromoCode');

    // Reward programs
    Route::get('/reward_programs', 'API\RewardProgramsController@index')->name('api.reward_programs.index');
    Route::get('/reward_programs/followouts_available', 'API\RewardProgramsController@followoutsAvailable')->name('api.reward_programs.followouts');
    Route::post('/reward_programs', 'API\RewardProgramsController@store')->name('api.reward_programs.store');
    Route::put('/reward_programs/{rewardProgram}', 'API\RewardProgramsController@update')->name('api.reward_programs.update');
    Route::get('/reward_programs/{rewardProgram}/pause', 'API\RewardProgramsController@pause')->name('api.reward_programs.pause');
    Route::get('/reward_programs/{rewardProgram}/resume', 'API\RewardProgramsController@resume')->name('api.reward_programs.resume');

    // Reward program jobs
    Route::get('/jobs', 'API\RewardProgramJobsController@index')->name('api.reward_program_jobs.index');
    Route::post('/jobs/redeem/{rewardProgramJob}', 'API\RewardProgramJobsController@redeem')->name('api.reward_program_jobs.redeem');
    Route::get('/jobs/mark-reward-as-received/{rewardProgramJob}', 'API\RewardProgramJobsController@markAsReceived')->name('api.reward_program_jobs.receive');
});
