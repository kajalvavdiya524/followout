<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Welcome page
Route::get('/', 'HomeController@welcome')->name('welcome');
Route::get('/landing-mobile', 'HomeController@welcomeMobile')->name('welcome-mobile');

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

// Route::get('/login/facebook', 'Auth\LoginController@redirectToFacebookProvider')->name('login.facebook');
// Route::get('/login/facebook/callback', 'Auth\LoginController@handleFacebookProviderCallback');
Route::get('/register_business', 'Auth\RegisterController@showRegistrationForm')->name('register.business');
Route::get('/register/social', 'Auth\RegisterController@showSocialRegistrationForm')->name('register.social');
Route::post('/register/social', 'Auth\RegisterController@registerFromSocial');

// About
Route::get('/about', 'HomeController@about')->name('about');

// Sales Representatives Agreement
Route::get('/sales_rep_agreement/{hash}', 'SalesRepresentativesController@salesRepAgreement')->name('sales-rep-agreement');
Route::post('/sales_rep_agreement/{hash}', 'SalesRepresentativesController@acceptSalesRepAgreement')->name('accept-sales-rep-agreement');

// Support
Route::post('/support/contact', 'HomeController@contactSupport')->middleware('throttle:10')->name('support.contact');

// Pricing
Route::redirect('/pricing', '/university', 301)->name('pricing');
// Route::get('/pricing', 'HomeController@pricing')->name('pricing');

// Followout University
Route::get('/university', 'HomeController@university')->name('university');

// Search
Route::get('/search/followouts', 'SearchController@followouts')->name('search.followouts');

// Followouts
Route::get('/followouts', 'FollowoutsController@index')->name('followouts.index');
Route::get('/followouts/create/{followhost?}', 'FollowoutsController@createManually')->name('followouts.create-manually')->middleware('auth');
Route::get('/followouts/{followout}', 'FollowoutsController@show')->name('followouts.show');
Route::get('/followouts/{followout}/virtual_address', 'FollowoutsController@goToVirtualAddress')->name('followouts.virtual-address.go');
Route::get('/followouts/preview/geo/{coupon}', 'FollowoutsController@previewGeoCouponFollowout')->name('followouts.preview.geo')->middleware('auth', 'role:followhost');

// Users by category
Route::get('/followees', 'UsersController@indexFollowees')->name('users.index.followees');
Route::get('/followhosts', 'UsersController@indexFollowhosts')->name('users.index.followhosts');

// Users
Route::get('/users/{user}', 'UsersController@show')->name('users.show');
Route::get('/users/{user}/avatar', 'UsersController@getAvatarFile')->name('users.avatar');
Route::get('/activate_account/{token}', 'UsersController@activateAccount');

// Coupons
Route::resource('coupons', 'CouponsController');
Route::get('/coupons/{coupon}/create_followout', 'CouponsController@createFollowout')->name('coupons.create-followout')->middleware('role:followhost');

// Reward programs
Route::resource('reward_programs', 'RewardProgramsController', ['except' => ['show', 'destroy']])->middleware('role:followhost');
Route::get('/reward_programs/{rewardProgram}/pause', 'RewardProgramsController@pause')->name('reward_programs.pause')->middleware('role:followhost');
Route::get('/reward_programs/{rewardProgram}/resume', 'RewardProgramsController@resume')->name('reward_programs.resume')->middleware('role:followhost');

// Payments
Route::post('/ajax-chargebee/iframe', 'OrderController@getChargebeeIframeUrl')->name('subscription-code.iframe');
Route::get('/ajax-chargebee/handle', 'OrderController@handleChargebeeSubscribeViaAjax')->name('subscribe.chargebee.handle-ajax');
Route::get('/ajax-chargebee/handle-mobile', 'OrderController@handleChargebeeSubscribeViaAjaxForMobileApp')->name('subscribe.chargebee.handle-ajax-mobile');

// Debug
if (app()->environment('production')) {
    Route::get('/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('auth', 'role:admin');
    Route::get('/php', 'AdminController@php')->middleware('auth', 'role:admin');
    Route::get('/test', 'HomeController@test')->middleware('auth', 'role:admin');
    Route::get('/test/notification', 'HomeController@sendTestNotification')->middleware('auth', 'role:admin');
} else {
    Route::get('/php', 'AdminController@php');
    Route::get('/test', 'HomeController@test');
    Route::get('/test/notification', 'HomeController@sendTestNotification')->middleware('auth');
    Route::get('/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
}

Route::middleware(['auth'])->group(function () {
    // Account activation
    Route::get('/account_activation', 'UsersController@askForAccountActivation');
    Route::get('/account_activation/resend', 'UsersController@resendAccountActivationEmail');

    // Search
    Route::get('/search/users', 'SearchController@users')->name('search.users');

    // Notifications
    Route::get('/notifications', 'NotificationsController@index')->name('notifications.index');
    Route::delete('/notifications', 'NotificationsController@destroyAll')->name('notifications.destroy.all');
    Route::get('/notifications/{notification}/read', 'NotificationsController@read')->name('notifications.read');
    Route::get('/notifications/mark-all-as-read', 'NotificationsController@readAll')->name('notifications.read-all');
    Route::delete('/notifications/{notification}', 'NotificationsController@destroy')->name('notifications.destroy');

    // Followouts
    Route::get('/followout_quick_create', 'FollowoutsController@create')->name('followouts.create')->middleware('auth');
    Route::post('/followouts', 'FollowoutsController@store')->name('followouts.store');
    Route::get('/followouts/{followout}/edit', 'FollowoutsController@edit')->name('followouts.edit');
    Route::put('/followouts/{followout}', 'FollowoutsController@update')->name('followouts.update');
    Route::delete('/followouts/{followout}', 'FollowoutsController@destroy')->name('followouts.destroy');
    Route::get('/followouts/{followout}/enable', 'FollowoutsController@enable')->name('followouts.enable');
    Route::get('/followouts/{followout}/disable', 'FollowoutsController@disable')->name('followouts.disable');
    Route::get('/followouts/{followout}/stats', 'FollowoutsController@stats')->name('followouts.stats');
    Route::post('/followouts/{followout}/invite_friends', 'FollowoutsController@inviteFriends')->name('followouts.invite-friends');
    Route::get('/followouts/{followout}/coupons/manage', 'FollowoutsController@manageCoupons')->name('followouts.coupons.edit');
    Route::post('/followouts/{followout}/coupons/attach/{coupon}', 'FollowoutsController@useCoupon')->name('followouts.coupons.use');
    Route::post('/followouts/{followout}/coupons/detach/{coupon}', 'FollowoutsController@disableCoupon')->name('followouts.coupons.disable');
    Route::post('/followouts/invite_attendee', 'FollowoutsController@inviteAttendee')->name('followouts.invite-attendee');

    // Followees
    Route::post('/followees/invite', 'FolloweesController@inviteFollowee')->name('followouts.invite');
    Route::post('/followees/invite_by_email', 'FolloweesController@inviteFolloweeByEmail')->name('followouts.invite-by-email');
    Route::get('/followees/present_request', 'FolloweesController@presentFollowoutRequest')->name('followouts.present-request');
    Route::get('/followees/present_request/{followout}/{user}/accept', 'FolloweesController@acceptPresentFollowoutRequest')->name('followouts.present-request.accept');
    Route::get('/followees/present_request/{followout}/{user}/decline', 'FolloweesController@declinePresentFollowoutRequest')->name('followouts.present-request.decline');
    Route::get('/followees/followee_intro/{user}', 'FolloweesController@sendFolloweeIntro')->name('users.followee-intro');
    Route::get('/followees/invitation/{followout}/manage', 'FolloweesController@manageFolloweeInvitation')->name('followouts.invitation.manage');
    Route::get('/followees/invitation/{followout}/accept', 'FolloweesController@acceptFolloweeInvitation')->name('followouts.invitation.accept');
    Route::get('/followees/invitation/{followout}/decline', 'FolloweesController@declineFolloweeInvitation')->name('followouts.invitation.decline');

    // Users
    Route::get('/me', 'UsersController@me')->name('me');
    Route::get('/users/{user}/edit', 'UsersController@edit')->name('users.edit');
    Route::put('/users/{user}', 'UsersController@update')->name('users.update');
    Route::get('/users/{user}/subscribe', 'UsersController@subscribe')->name('users.subscribe');
    Route::get('/users/{user}/unsubscribe', 'UsersController@unsubscribe')->name('users.unsubscribe');
    Route::delete('/users/{user}', 'AdminController@deleteUser')->name('users.destroy');
    Route::get('/deactivate_account', 'UsersController@accountDeletionConfirmation')->name('users.suicide');
    Route::post('/deactivate_account', 'UsersController@requestAccountDeletion')->name('users.suicide.confirmed');

    // Reward program jobs
    Route::get('/jobs', 'RewardProgramJobsController@index')->name('reward_program_jobs.index');
    Route::post('/jobs/redeem/{rewardProgramJob}', 'RewardProgramJobsController@redeem')->name('reward_program_jobs.redeem');
    Route::get('/jobs/mark-reward-as-received/{rewardProgramJob}', 'RewardProgramJobsController@markAsReceived')->name('reward_program_jobs.receive');
    Route::get('/jobs/toggle-dispute/{rewardProgramJob}', 'RewardProgramJobsController@toggleDispute')->name('reward_program_jobs.dispute.toggle');
    Route::get('/jobs/resolve-all-disputes', 'RewardProgramJobsController@resolveAllDisputes')->name('reward_program_jobs.disputes.resolve-all');

    // Messages
    // Route::get('/messages', 'MessagesController@chats')->name('messages.chats');
    // Route::get('/messages/{chatId}', 'MessagesController@chat')->name('messages.chat');

    // Shopping Cart
    Route::get('/cart', 'OrderController@cart')->name('cart');
    Route::get('/cart/add/{product}', 'OrderController@addProduct')->name('cart.add');
    Route::get('/cart/remove/{id}', 'OrderController@removeItem')->name('cart.remove');
    Route::get('/checkout', 'OrderController@payment')->name('checkout');
    Route::get('/free_subscription', 'OrderController@activateFreeSubscription')->name('subscribe.free');
    Route::get('/chargebee', 'OrderController@subscribeViaChargeBee')->name('subscribe.chargebee');
    Route::get('/chargebee/redirect', 'OrderController@redirectToChargebee')->name('subscribe.chargebee-redirect');
    Route::get('/chargebee/handle', 'OrderController@handleSubscribeViaChargeBee')->name('subscribe.chargebee.handle');
    Route::post('/checkout', 'OrderController@pay')->name('checkout.pay');

    // Payments
    Route::get('/payments', 'PaymentsController@index')->middleware('role:admin')->name('payments.index');
    Route::get('/payments/{payment}', 'PaymentsController@show')->name('payments.show');

    // Payouts
    Route::get('/payouts', 'PayoutsController@index')->name('payouts.index');
    Route::get('/payouts/create', 'PayoutsController@create')->name('payouts.create');
    Route::post('/payouts', 'PayoutsController@store')->name('payouts.store');
    Route::get('/payouts/{payout}', 'PayoutsController@show')->name('payouts.show');
    Route::get('/payouts/{payout}/approve', 'PayoutsController@approve')->name('payouts.approve');
    Route::get('/payouts/{payout}/cancel', 'PayoutsController@cancel')->name('payouts.cancel');

    // Settings
    Route::get('/settings/account', 'SettingsController@accountTab')->name('settings.account');
    Route::get('/settings/security', 'SettingsController@securityTab')->name('settings.security');
    Route::get('/settings/payments', 'SettingsController@paymentsTab')->name('settings.payments');
    Route::get('/settings/notifications', 'SettingsController@notificationsTab')->name('settings.notifications');
    Route::post('/settings/notifications', 'SettingsController@updateNotificationSettings')->name('settings.notifications.update');
    Route::post('/settings/followouts', 'SettingsController@updateFollowoutSettings')->name('settings.followouts.update');
    Route::post('/settings/password', 'SettingsController@changePassword')->name('settings.password.change');
    Route::post('/settings/sales_rep', 'SettingsController@setSalesRepCode')->name('settings.sales-rep');
    Route::post('/settings/subscription_code', 'SettingsController@useSubscriptionCode')->name('settings.subscription-code.activate');
    Route::get('/settings/social_accounts/disconnect/{provider}', 'SettingsController@disconnectSoicalAccount')->name('settings.social.disconnect');

    // Subscriptions
    Route::get('/subscription/resume', 'SubscriptionsController@resume')->name('subscription.resume');
    Route::get('/subscription/cancel', 'SubscriptionsController@cancel')->name('subscription.cancel');

    // Manage users
    Route::get('/login_as/{user}', 'AdminController@loginAsUser')->middleware('role:admin')->name('login-as-user');
    Route::get('/manage_users', 'AdminController@manageUsers')->middleware('role:admin')->name('users.manage.index');
    Route::get('/manage_users/decline_account_deletion/{user}', 'AdminController@declineAccountDeletionRequest')->middleware('role:admin')->name('users.manage.decline-account-deletion');
    Route::get('/manage_users/give/default_followout/{user}', 'AdminController@updateOrCreateDefaultFollowout')->middleware('role:admin')->name('users.manage.update-default-followout');
    Route::get('/manage_users/give/role/{user}/{role}', 'AdminController@setUserRole')->middleware('role:admin')->name('users.change-role');
    Route::post('/manage_users/give/subscription', 'AdminController@giveSubscription')->middleware('role:admin')->name('users.manage.give-subscription');
    Route::post('/manage_users/remove/subscription', 'AdminController@removeSubscription')->middleware('role:admin')->name('users.manage.remove-subscription');
    Route::post('/manage_users/set_sales_rep_code/{user}', 'AdminController@setSalesRepCode')->middleware('role:admin')->name('users.manage.sales-rep');

    // Manage pages
    Route::get('/app/static_content/edit', 'AdminController@editStaticContent')->middleware('role:admin')->name('app.static-content.edit');
    Route::put('/app/pages/landing', 'AdminController@updateLandingPage')->middleware('role:admin')->name('app.pages.landing.update');
    Route::put('/app/pages/sales_rep_agreement', 'AdminController@updateSalesRepAgreement')->middleware('role:admin')->name('app.pages.sales-rep-agreement.update');
    Route::put('/app/pages/about', 'AdminController@updateAboutPage')->middleware('role:admin')->name('app.pages.about.update');
    Route::put('/app/pages/university', 'AdminController@updateUniversityPage')->middleware('role:admin')->name('app.pages.university.update');
    Route::put('/app/pages/users', 'AdminController@updateUsersPage')->middleware('role:admin')->name('app.pages.users.update');

    // Products
    Route::get('/app/products', 'ProductsController@index')->name('products.index');
    Route::get('/app/products/{product}/edit', 'ProductsController@edit')->name('products.edit');
    Route::put('/app/products/{product}', 'ProductsController@update')->name('products.update');

    // Promo codes
    Route::get('/promo_codes/create', 'ProductsController@createPromoCode')->name('promo-codes.create');
    Route::post('/promo_codes', 'ProductsController@storePromoCode')->name('promo-codes.store');
    Route::delete('/promo_codes/{code}', 'ProductsController@destroyPromoCode')->name('promo-codes.destroy');

    // Sales representatives
    Route::get('/sales_reps', 'SalesRepresentativesController@index')->name('sales-reps.index');
    Route::get('/sales_reps/create', 'SalesRepresentativesController@create')->name('sales-reps.create');
    Route::post('/sales_reps', 'SalesRepresentativesController@store')->name('sales-reps.store');
    Route::delete('/sales_reps/{id}', 'SalesRepresentativesController@destroy')->name('sales-reps.destroy');

    // Experience categories
    Route::get('/app/content/experience_categories', 'ExperienceCategoriesController@index')->name('app.experience-categories.index');
    Route::get('/app/content/experience_categories/{category}/edit', 'ExperienceCategoriesController@edit')->name('app.experience-categories.edit');
    Route::put('/app/content/experience_categories/{category}', 'ExperienceCategoriesController@update')->name('app.experience-categories.update');

    // App
    Route::get('/app/deploy', 'AdminController@confirmDeploy')->middleware('role:admin')->name('app.deploy');
    Route::get('/app/deploy/authorized', 'AdminController@deploy')->middleware('role:admin')->name('app.deploy.authorized');
});
