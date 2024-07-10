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
Route::post('image/upload', 'ImageUploadController@upload');
Route::get('cronjob', 'CronJobController@run')->name('cronjob')->middleware('demo:GET');
Route::middleware('maintenance')->group(function () {
    Auth::routes(['verify' => true]);
    Route::post('cookie/accept', 'GeneralController@cookie')->middleware('ajax.only');
    Route::group(['namespace' => 'Auth'], function () {
        Route::get('login', 'LoginController@showLoginForm')->name('login');
        Route::post('login', 'LoginController@login');
        Route::post('logout', 'LoginController@logout')->name('logout');
        Route::middleware(['registration.disable'])->group(function () {
            Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
            Route::post('register', 'RegisterController@register')->middleware('trustip');
        });
        Route::name('oauth.')->prefix('oauth')->group(function () {
            Route::middleware('demo:GET')->group(function () {
                Route::get('{provider}', 'OAuthController@redirectToProvider')->name('login')->middleware('trustip');
                Route::get('{provider}/callback', 'OAuthController@handleProviderCallback')->name('callback');
            });
            Route::middleware('auth')->group(function () {
                Route::get('data/complete', 'OAuthController@showCompleteForm');
                Route::post('data/complete', 'OAuthController@complete')->name('data.complete')->middleware('trustip');
            });
        });
        Route::middleware('smtp')->group(function () {
            Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
            Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        });
        Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');
        Route::middleware('oauth.complete')->group(function () {
            Route::middleware('smtp')->group(function () {
                Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
                Route::post('email/verify/email/change', 'VerificationController@changeEmail')->name('change.email');
                Route::get('email/verify/{id}/{hash}', 'VerificationController@verify')->name('verification.verify');
                Route::post('email/resend', 'VerificationController@resend')->name('verification.resend');
            });
            Route::middleware(['auth', 'verified'])->group(function () {
                Route::get('2fa/verify', 'TwoFactorController@show2FaVerifyForm');
                Route::post('2fa/verify', 'TwoFactorController@verify2fa')->name('2fa.verify');
            });
        });
    });
    Route::prefix('workspace')->namespace('Workspace')
        ->middleware(['auth', 'oauth.complete', 'verified', '2fa.verify'])->group(function () {

        Route::name('workspace.')->group(function () {

            Route::get('/', function () {
                return redirect()->route(authUser()->isAuthor() ? 'workspace.dashboard' : 'workspace.purchases.index');
            })->name('index');

            Route::get('dashboard', 'DashboardController@index')->name('dashboard')->middleware('author');

            Route::prefix('become-an-author')->middleware('not.author')->group(function () {
                Route::get('/', 'AuthorController@becomeAnAuthor')->name('become-an-author');
                Route::post('/', 'AuthorController@becomeAnAuthorAction');
            });

            Route::name('items.')->prefix('items')->middleware('author')->group(function () {
                Route::get('/', 'ItemController@index')->name('index');
                Route::get('create', 'ItemController@create')->name('create')->middleware('kyc.required');
                Route::post('store', 'ItemController@store')->name('store');
                Route::get('{id}/edit', 'ItemController@edit')->name('edit');
                Route::post('{id}/update', 'ItemController@update')->name('update');

                Route::name('changelogs.')->prefix('{id}/changelogs')->middleware('item_changelogs.disable')->group(function () {
                    Route::get('/', 'ItemController@changelogs')->name('index');
                    Route::post('store', 'ItemController@changelogsStore')->name('store');
                    Route::delete('delete/{changelog_id}', 'ItemController@changelogsDelete')->name('delete');
                });

                Route::get('{id}/history', 'ItemController@history')->name('history');
                Route::middleware('discount.disable')->group(function () {
                    Route::get('{id}/discount', 'ItemController@discount')->name('discount');
                    Route::post('{id}/discount', 'ItemController@discountCreate')->name('discount.create');
                    Route::post('{id}/discount/delete', 'ItemController@discountDelete')->name('discount.delete');
                });
                Route::get('{id}/statistics', 'ItemController@statistics')->name('statistics');
                Route::post('files/load', 'ItemController@loadFiles')->name('files.load');
                Route::delete('files/{id}/delete', 'ItemController@deleteFile')->name('files.delete');
                Route::post('upload', 'UploadController@upload')->name('upload');
                Route::post('{id}/download', 'ItemController@download')->name('download');
                Route::delete('{id}/delete', 'ItemController@destroy')->name('destroy');
            });

            Route::name('purchases.')->prefix('purchases')->group(function () {
                Route::get('/', 'PurchaseController@index')->name('index');
                Route::get('{id}/license', 'PurchaseController@license')->name('license');
                Route::get('{id}/download', 'PurchaseController@download')->name('download');
            });

            Route::name('transactions.')->prefix('transactions')->group(function () {
                Route::get('/', 'TransactionController@index')->name('index');
                Route::get('{id}', 'TransactionController@show')->name('show');
                Route::get('{id}/invoice', 'TransactionController@invoice')->name('invoice');
            });

            Route::middleware('author')->group(function () {
                Route::get('referrals', 'ReferralController@index')->name('referrals')->middleware('referral.disable');
                Route::name('withdrawals.')->prefix('withdrawals')->group(function () {
                    Route::get('/', 'WithdrawalController@index')->name('index');
                    Route::post('/', 'WithdrawalController@withdraw')->name('withdraw')->middleware('kyc.required');
                });
            });

            Route::get('statements', 'StatementController@index')->name('statements');

            Route::name('refunds.')->prefix('refunds')->middleware('refunds.disable')->group(function () {
                Route::get('/', 'RefundController@index')->name('index');
                Route::get('create', 'RefundController@create')->name('create');
                Route::post('store', 'RefundController@store')->name('store');
                Route::get('{id}', 'RefundController@show')->name('show');
                Route::post('{id}/reply', 'RefundController@reply')->name('reply');
                Route::post('{id}/accept', 'RefundController@accept')->name('accept');
                Route::post('{id}/decline', 'RefundController@decline')->name('decline');
            });

            Route::name('tickets.')->prefix('tickets')->middleware('tickets.disable')->group(function () {
                Route::get('/', 'TicketController@index')->name('index');
                Route::get('create', 'TicketController@create')->name('create');
                Route::post('create', 'TicketController@store')->name('store');
                Route::get('{id}', 'TicketController@show')->name('show');
                Route::post('{id}', 'TicketController@reply')->name('reply');
                Route::get('{id}/{attachment_id}/download', 'TicketController@download')->name('download');
            });

            Route::name('tools.')->prefix('tools')->namespace('Tools')->group(function () {
                Route::get('/', function () {
                    return redirect()->route('workspace.index');
                })->name('index');
                if (config('system.install.complete') && isAddonActive('license_verification_tool')) {
                    Route::name('license-verification.')->prefix('license-verification')->group(function () {
                        Route::get('/', 'LicenseVerificationController@index')->name('index');
                        Route::post('/', 'LicenseVerificationController@verify')->name('verify');
                    });
                }
            });

            Route::name('settings.')->prefix('settings')->group(function () {
                Route::get('/', 'SettingsController@index')->name('index');
                Route::post('/', 'SettingsController@detailsUpdate')->name('update');
                Route::get('profile', 'SettingsController@profile')->name('profile');
                Route::post('profile', 'SettingsController@profileUpdate')->name('profile.update');
                Route::middleware('author')->group(function () {
                    Route::get('withdrawal', 'SettingsController@withdrawal')->name('withdrawal');
                    Route::post('withdrawal', 'SettingsController@withdrawalUpdate')->name('withdrawal.update');
                });
                Route::middleware('api.disable')->group(function () {
                    Route::get('api-key', 'SettingsController@apiKey')->name('api-key');
                    Route::post('api-key/generate', 'SettingsController@apiKeyGenerate')->name('api-key.generate');
                });
                Route::get('badges', 'SettingsController@badges')->name('badges');
                Route::post('badges/sortable', 'SettingsController@badgesSortable')->name('badges.sortable');
                Route::get('password', 'SettingsController@password')->name('password');
                Route::post('password', 'SettingsController@passwordUpdate')->name('password.update');
                Route::get('2fa', 'SettingsController@towFactor')->name('2fa');
                Route::post('2fa/enable', 'SettingsController@towFactorEnable')->name('2fa.enable');
                Route::post('2fa/disabled', 'SettingsController@towFactorDisable')->name('2fa.disable');
                Route::middleware('kyc.disable')->group(function () {
                    Route::get('kyc', 'SettingsController@kyc')->name('kyc');
                    Route::post('kyc', 'SettingsController@kycStore')->name('kyc.store');
                });
            });
        });
    });
});

Route::middleware(['oauth.complete', 'verified', '2fa.verify'])->group(function () {
    Route::get('/', 'HomeController@index')
        ->name('home')->middleware('referral');

    Route::middleware('maintenance')->group(function () {
        Route::get('favorites', 'GeneralController@favorites')
            ->name('favorites')->middleware('auth');

        Route::name('categories.')->prefix('categories')->group(function () {
            Route::get('/', 'CategoryController@index')->name('index');
            Route::get('{category_slug}', 'CategoryController@category')->name('category');
            Route::get('{category_slug}/{sub_category_slug}', 'CategoryController@subCategory')->name('sub-category');
        });

        Route::name('items.')->prefix('items')->group(function () {
            Route::get('/', 'ItemController@index')->name('index');
            Route::get('preview/{id}', 'ItemController@preview')->name('preview');
            Route::middleware(['auth', 'oauth.complete', 'verified', '2fa.verify', 'kyc.required'])->group(function () {
                Route::post('download/{id}', 'ItemController@download')->name('download');
                Route::get('download/{id}/external', 'ItemController@externalDownload')->name('download.external');
            });
            Route::middleware('item.views')->group(function () {
                Route::get('{slug}/{id}', 'ItemController@view')->name('view');
                Route::post('{slug}/{id}/buy-now', 'ItemController@buyNow')->name('buy-now')->middleware(['auth', 'buy_now.disable']);
                Route::get('{slug}/{id}/changelogs', 'ItemController@changelogs')->name('changelogs')->middleware('item_changelogs.disable');
                Route::middleware('item_reviews.disable')->group(function () {
                    Route::get('{slug}/{id}/reviews', 'ItemController@reviews')->name('reviews');
                    Route::get('{slug}/{id}/reviews/{review_id}', 'ItemController@review')->name('review');
                    Route::middleware('auth')->group(function () {
                        Route::post('{slug}/{id}/reviews', 'ItemController@reviewsStore')->name('reviews.store');
                        Route::post('{slug}/{id}/reviews/{review_id}/reply', 'ItemController@reviewsReply')->name('reviews.reply');
                    });
                });
                Route::middleware('item_comments.disable')->group(function () {
                    Route::get('{slug}/{id}/comments', 'ItemController@comments')->name('comments');
                    Route::get('{slug}/{id}/comments/{comment_id}', 'ItemController@comment')->name('comment');
                });
            });
        });

        Route::name('cart.')->prefix('cart')->group(function () {
            Route::get('/', 'CartController@index')->name('index');
            Route::post('add-item', 'CartController@addItem')->name('add-item');
            Route::post('update-item/{id}', 'CartController@updateItem')->name('update-item');
            Route::post('remove-item/{id}', 'CartController@removeItem')->name('remove-item');
            Route::post('empty', 'CartController@empty')->name('empty');
        });

        Route::middleware(['auth', 'oauth.complete', 'verified', '2fa.verify', 'kyc.required'])->group(function () {
            Route::post('cart/checkout', 'CartController@checkout')->name('cart.checkout');
            Route::name('checkout.')->prefix('checkout')->group(function () {
                Route::get('{id}', 'CheckoutController@index')->name('index');
                Route::post('{id}', 'CheckoutController@process')->name('process')->middleware('trustip');
            });
        });

        Route::name('profile.')->prefix('user')->group(function () {
            Route::middleware('lowercase')->group(function () {
                Route::get('{username}', 'ProfileController@index')->name('index');
                Route::get('{username}/portfolio', 'ProfileController@portfolio')->name('portfolio');
                Route::get('{username}/followers', 'ProfileController@followers')->name('followers');
                Route::get('{username}/following', 'ProfileController@following')->name('following');
                Route::get('{username}/reviews', 'ProfileController@reviews')->name('reviews')->middleware('item_reviews.disable');
            });
            Route::post('{username}/sendmail', 'ProfileController@sendMail')->name('sendmail')->middleware('demo');
        });

        Route::name('blog.')->prefix('blog')->middleware('blog.disable')->group(function () {
            Route::get('/', 'BlogController@index')->name('index');
            Route::get('categories', 'BlogController@categories')->name('categories');
            Route::get('categories/{slug}', 'BlogController@category')->name('category');
            Route::get('articles', function () {
                return redirect()->route('blog.index');
            });
            Route::get('articles/{slug}', 'BlogController@article');
            Route::post('articles/{slug}', 'BlogController@comment')->name('article');
        });

        Route::middleware(['contact.disable', 'smtp'])->group(function () {
            Route::get('contact-us', 'GeneralController@contact');
            Route::post('contact-us', 'GeneralController@contactSend')->name('contact');
        });

        Route::get('api-docs', 'ApiDocsController@index')->name('api.docs')->middleware('api.disable');

        Route::get('{slug}', 'GeneralController@page')->name('page');
    });
});