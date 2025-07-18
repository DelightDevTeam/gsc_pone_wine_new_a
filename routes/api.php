<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\HomeController;
use App\Http\Controllers\Api\V1\Bank\BankController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DepositRequestController;
use App\Http\Controllers\Api\V1\Game\DirectLaunchGameController;
use App\Http\Controllers\Api\V1\Game\LaunchGameController;
use App\Http\Controllers\Api\V1\GetAdminSiteLogoNameController;
use App\Http\Controllers\Api\V1\PoneWineBetController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ShanTransactionController;
use App\Http\Controllers\Api\V1\Slot\GameController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\WagerController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\BonusController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\BuyInController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\BuyOutController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\CancelBetController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\GameResultController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\GetBalanceController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\GetGameListController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\JackPotController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\MobileLoginController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\PlaceBetController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\PushBetController;
use App\Http\Controllers\Api\V1\Webhook\Gsc\RollbackController;
use App\Http\Controllers\Api\V1\WithDrawRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ShanGetBalanceController;
use App\Http\Controllers\Api\V1\ShanLaunchGameController;


// home route
require_once __DIR__.'/user.php';

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/player-change-password', [AuthController::class, 'playerChangePassword']);
Route::post('/logout', [AuthController::class, 'logout']);

//get game list operator
Route::post('/operatorgetgamelist', [GetGameListController::class, 'getGameList']);

// sameless route
Route::group(['prefix' => 'Seamless'], function () {
    Route::post('GetBalance', [GetBalanceController::class, 'getBalance']);
    Route::post('PlaceBet', [PlaceBetController::class, 'placeBet']);
    Route::post('GameResult', [GameResultController::class, 'gameResult']);
    Route::post('Rollback', [RollbackController::class, 'rollback']);
    // // Route::group(["middleware" => ["webhook_log"]], function(){
    // // Route::post('GetGameList', [LaunchGameController::class, 'getGameList']);
    Route::post('CancelBet', [CancelBetController::class, 'cancelBet']);
    Route::post('BuyIn', [BuyInController::class, 'buyIn']);
    Route::post('BuyOut', [BuyOutController::class, 'buyOut']);
    Route::post('PushBet', [PushBetController::class, 'pushBet']);
    Route::post('Bonus', [BonusController::class, 'bonus']);
    Route::post('Jackpot', [JackPotController::class, 'jackPot']);
    Route::post('MobileLogin', [MobileLoginController::class, 'MobileLogin']);
    // });
});

Route::post('bet', [PoneWineBetController::class, 'index'])->middleware('transaction');
Route::post('transactions', [ShanTransactionController::class, 'index'])->middleware('transaction');
Route::delete('/game-lists-delete', [GameController::class, 'deleteGameLists']);

// for slot
Route::post('/transaction-details/{tranId}', [TransactionController::class, 'getTransactionDetails']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    //Route::get('home', [HomeController::class, 'home']);
    Route::post('GameLogin', [LaunchGameController::class, 'LaunchGame']);
    Route::get('wager-logs', [WagerController::class, 'index']);
    Route::get('user', [AuthController::class, 'getUser']);
    Route::get('agent', [AuthController::class, 'getAgent']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('profile', [AuthController::class, 'profile']);
    Route::get('agentPaymentType', [BankController::class, 'all']);
    Route::post('deposit', [DepositRequestController::class, 'deposit']);
    Route::get('paymentType', [BankController::class, 'paymentType']);
    Route::post('withdraw', [WithDrawRequestController::class, 'withdraw']);
    Route::get('depositlog', [DepositRequestController::class, 'log']);
    Route::get('withdrawlog', [WithDrawRequestController::class, 'log']);
    Route::get('sitelogo-name', [GetAdminSiteLogoNameController::class, 'GetSiteLogoAndSiteName']);

    Route::get('shan-transactions', [TransactionController::class, 'GetPlayerShanReport']);

    Route::get('contact', [ContactController::class, 'get']);
    Route::get('promotion', [PromotionController::class, 'index']);
    Route::get('winnerText', [BannerController::class, 'winnerText']);
    Route::get('banner_Text', [BannerController::class, 'bannerText']);
    Route::get('popup-ads-banner', [BannerController::class, 'AdsBannerIndex']);
    Route::get('banner', [BannerController::class, 'index']);
    Route::get('videoads', [BannerController::class, 'ApiVideoads']);
    Route::get('toptenwithdraw', [BannerController::class, 'TopTen']);

    //slot
    Route::get('gameTypeProducts/{id}', [GameController::class, 'gameTypeProducts']);
    Route::get('allGameProducts', [GameController::class, 'allGameProducts']);
    // Route::get('game_types', [GameController::class, 'gameType']);
    // Route::get('hot_game_lists', [GameController::class, 'HotgameList']);
    Route::get('pphotgamelist', [GameController::class, 'PPHotgameList']);
    //Route::get('gamelist/{provider_id}/{game_type_id}/', [GameController::class, 'gameList']);
    //Route::get('slotfishgamelist/{provider_id}/{game_type_id}/', [GameController::class, 'JILIgameList']);
    //Route::get('gameFilter', [GameController::class, 'gameFilter']);
    //Route::get('gamelistTest/{provider_id}/{game_type_id}/', [GameController::class, 'gameListTest']);
    Route::get('ponewine-report', [ReportController::class, 'index']);

    // gsc
    // Route::group(['prefix' => 'game'], function () {
    //     Route::post('Seamless/LaunchGame', [LaunchGameController::class, 'launchGame']);
    //     Route::get('gamelist/{provider_id}/{game_type_id}', [GameController::class, 'gameList']);
    // });

    // Route::group(['prefix' => 'direct'], function () {
    //     Route::post('Seamless/LaunchGame', [DirectLaunchGameController::class, 'launchGame']);
    // });
});

Route::group(['prefix' => 'shan'], function () {
    Route::post('balance', [ShanGetBalanceController::class, 'shangetbalance']);
    Route::post('launch-game', [ShanLaunchGameController::class, 'launch']);

});