<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ajaxController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ClosingsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\LevelMembersController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PackageRequestController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Models\Sms;
use Symfony\Component\HttpKernel\DataCollector\AjaxDataCollector;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/register/success/{own_id}', [RegisterController::class, 'registerSuccess'])->name('registerSuccess');
Route::get('/set/direct/business', [ajaxController::class, 'setDirectBusinessAll'])->name('setDirectBusiness');
Route::get('/sendRoi', [ajaxController::class, 'sendRoiAndLevel'])->name('sendRoi');
Route::get('/checkRewards', [ajaxController::class, 'checkRewards'])->name('checkRewards');

Route::post('/checkSponsor', [ajaxController::class, 'checkSponsor'])->name('checkSponsor');
Route::get('/repair', [ajaxController::class, 'repair'])->name('repair');

Route::middleware(['auth'])->group(function () {

Route::post('/pay/closing',[ClosingsController::class,'payClosing'])->name('payClosing');



Route::get('/closings', [ClosingsController::class, 'showClosings'])->name('closings');
Route::get('/closings/details/{date}', [ClosingsController::class, 'showClosingsDetail'])->name('closingsDetail');

Route::get('/rewards', [RewardController::class, 'showRewards'])->name('showRewards');
Route::get('/rewards/{reward_id}', [RewardController::class, 'rewardAchievers'])->name('rewardAchievers');
Route::get('/my/rewards', [RewardController::class, 'rewardsAchieved'])->name('rewardsAchieved');



Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/all-users/{type}', [AdminController::class, 'showAllUsers'])->name('allUsers');
Route::get('/login/user/{id}', [AdminController::class, 'loginUser'])->name('loginUser');
Route::get('/company/business', [AdminController::class, 'showBusiness'])->name('companyBusiness');
Route::get('company//business/detail/{date}', [AdminController::class, 'showBusinessDetail'])->name('companyBusinessDetail');

Route::get('/user/status/{userid}',[UserController::class,'changeUserStatus'])->name('changeUserStatus');

Route::get('/level/members/{ownid?}',[LevelMembersController::class,'showLevelmemebrs'])->name('levelMembers');
Route::get('/level/tree/{ownid?}',[LevelMembersController::class,'showLevelTree'])->name('levelTree');

Route::resource('/package',PackageController::class);
Route::resource('/company',CompanyController::class);

Route::get('package/change-status/{id}',[PackageController::class,'changePackageStatus'])->name('changePackageStatus');


Route::get('package-request',[PackageRequestController::class,'index'])->name('packageRequest.index');
Route::post('package-request/package',[PackageRequestController::class,'apply'])->name('packageRequest.apply');
Route::get('package-requests/{type?}',[PackageRequestController::class,'showRequests'])->name('packageRequest.show');
Route::get('package-requests/update/{id}/{type}',[PackageRequestController::class,'updateRequest'])->name('packageRequest.update');


Route::get('kyc-request',[KycController::class,'index'])->name('kycRequest.index');
Route::post('kyc-request/kyc',[KycController::class,'apply'])->name('kycRequest.apply');
Route::get('kyc-requests/{type?}',[KycController::class,'showRequests'])->name('kycRequest.show');
Route::get('kyc-requests/update/{id}/{type}',[KycController::class,'updateRequest'])->name('kycRequest.update');


Route::get('income-report/{type}',[IncomeController::class,'index'])->name('incomeReoprt.index');
Route::get('withdraw-report',[WithdrawController::class,'index'])->name('withdraw.index');


Route::get('profile/{userid?}',[HomeController::class,'showProfile'])->name('profile');
Route::post('profile/{userid?}',[HomeController::class,'updateProfile'])->name('profile.update');
Route::post('profile/password/{userid?}',[HomeController::class,'changePassword'])->name('profile.password.chage');

});

