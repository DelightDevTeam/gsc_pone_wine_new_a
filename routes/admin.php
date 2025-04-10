<?php

use App\Http\Controllers\Admin\AdsVedioController;
use App\Http\Controllers\Admin\TopTenWithdrawController;
use App\Http\Controllers\Admin\WinnerTextController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerAdsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BannerTextController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DepositRequestController;
use App\Http\Controllers\Admin\GameListController;
use App\Http\Controllers\Admin\GameTypeProductController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SeniorController;
use App\Http\Controllers\Admin\SubAccountController;
use App\Http\Controllers\Admin\SuperController;
use App\Http\Controllers\Admin\TransferLog\TransferLogController;
use App\Http\Controllers\Admin\WithDrawRequestController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResultArchiveController;
use App\Http\Controllers\Admin\DailySummaryController;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['auth', 'checkBanned'],
], function () {

    Route::post('balance-up', [HomeController::class, 'balanceUp'])->name('balanceUp');
    Route::get('logs/{id}', [HomeController::class, 'logs'])
        ->name('logs');

    // Roles
    Route::delete('roles/destroy', [RolesController::class, 'massDestroy'])->name('roles.massDestroy');
    Route::resource('roles', RolesController::class);

    Route::get('/changePassword/{user}', [HomeController::class, 'changePassword'])->name('changePassword');
    Route::post('/updatePassword/{user}', [HomeController::class, 'updatePassword'])->name('updatePassword');

    Route::get('/changeplayersite/{user}', [HomeController::class, 'changePlayerSite'])->name('changeSiteName');

    Route::post('/updatePlayersite/{user}', [HomeController::class, 'updatePlayerSiteLink'])->name('updateSiteLink');

    Route::get('/player-list', [HomeController::class, 'playerList'])->name('playerList');

    // Players
    Route::delete('user/destroy', [PlayerController::class, 'massDestroy'])->name('user.massDestroy');
    Route::put('player/{id}/ban', [PlayerController::class, 'banUser'])->name('player.ban');
    Route::resource('player', PlayerController::class);
    Route::get('player-cash-in/{player}', [PlayerController::class, 'getCashIn'])->name('player.getCashIn');
    Route::post('player-cash-in/{player}', [PlayerController::class, 'makeCashIn'])->name('player.makeCashIn');
    Route::get('player/cash-out/{player}', [PlayerController::class, 'getCashOut'])->name('player.getCashOut');
    Route::post('player/cash-out/update/{player}', [PlayerController::class, 'makeCashOut'])
        ->name('player.makeCashOut');
    Route::get('player-changepassword/{id}', [PlayerController::class, 'getChangePassword'])->name('player.getChangePassword');
    Route::post('player-changepassword/{id}', [PlayerController::class, 'makeChangePassword'])->name('player.makeChangePassword');
    Route::get('/players-list', [PlayerController::class, 'player_with_agent'])->name('playerListForAdmin');

    Route::resource('video-upload', AdsVedioController::class);
    Route::resource('winner_text', WinnerTextController::class);
    Route::resource('top-10-withdraws', TopTenWithdrawController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('adsbanners', BannerAdsController::class);
    Route::resource('text', BannerTextController::class);
    Route::resource('/promotions', PromotionController::class);
    Route::resource('contact', ContactController::class);
    Route::resource('paymentTypes', PaymentTypeController::class);
    Route::resource('bank', BankController::class);

    // provider Game Type Start
    Route::get('gametypes', [GameTypeProductController::class, 'index'])->name('gametypes.index');
    Route::get('gametypes/{game_type_id}/product/{product_id}', [GameTypeProductController::class, 'edit'])->name('gametypes.edit');
    Route::post('gametypes/{game_type_id}/product/{product_id}', [GameTypeProductController::class, 'update'])->name('gametypes.update');
    // provider Game Type End

    Route::post('/mark-notifications-read', function () {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    })->name('markNotificationsRead');

    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');

    // game list start
    Route::get('all-game-lists', [GameListController::class, 'index'])->name('gameLists.index');
    Route::get('all-game-lists/{id}', [GameListController::class, 'edit'])->name('gameLists.edit');
    Route::post('all-game-lists/{id}', [GameListController::class, 'update'])->name('gameLists.update');

    Route::patch('gameLists/{id}/toggleStatus', [GameListController::class, 'toggleStatus'])->name('gameLists.toggleStatus');

    Route::patch('hotgameLists/{id}/toggleStatus', [GameListController::class, 'HotGameStatus'])->name('HotGame.toggleStatus');

    // pp hot

    Route::patch('pphotgameLists/{id}/toggleStatus', [GameListController::class, 'PPHotGameStatus'])->name('PPHotGame.toggleStatus');
    Route::get('game-list/{gameList}/edit', [GameListController::class, 'edit'])->name('game_list.edit');
    Route::post('/game-list/{id}/update-image-url', [GameListController::class, 'updateImageUrl'])->name('game_list.update_image_url');
    Route::get('game-list-order/{gameList}/edit', [GameListController::class, 'GameListOrderedit'])->name('game_list_order.edit');
    Route::post('/game-lists/{id}/update-order', [GameListController::class, 'updateOrder'])->name('GameListOrderUpdate');

    // game list end
    Route::resource('agent', AgentController::class);
    Route::get('agent-player-report/{id}', [AgentController::class, 'getPlayerReports'])->name('agent.getPlayerReports');
    Route::get('agent-cash-in/{id}', [AgentController::class, 'getCashIn'])->name('agent.getCashIn');
    Route::post('agent-cash-in/{id}', [AgentController::class, 'makeCashIn'])->name('agent.makeCashIn');
    Route::get('agent/cash-out/{id}', [AgentController::class, 'getCashOut'])->name('agent.getCashOut');
    Route::post('agent/cash-out/update/{id}', [AgentController::class, 'makeCashOut'])
        ->name('agent.makeCashOut');
    Route::put('agent/{id}/ban', [AgentController::class, 'banAgent'])->name('agent.ban');
    Route::get('agent-changepassword/{id}', [AgentController::class, 'getChangePassword'])->name('agent.getChangePassword');
    Route::post('agent-changepassword/{id}', [AgentController::class, 'makeChangePassword'])->name('agent.makeChangePassword');
    Route::resource('subacc', SubAccountController::class);
    Route::resource('master', MasterController::class);
    Route::resource('owner', OwnerController::class);
    Route::resource('super', SuperController::class);
    Route::resource('senior', SeniorController::class);

    Route::put('subacc/{id}/ban', [SubAccountController::class, 'banSubAcc'])->name('subacc.ban');
    Route::get('subacc-changepassword/{id}', [SubAccountController::class, 'getChangePassword'])->name('subacc.getChangePassword');
    Route::post('subacc-changepassword/{id}', [SubAccountController::class, 'makeChangePassword'])->name('subacc.makeChangePassword');
    Route::get('owner-player-list', [OwnerController::class, 'OwnerPlayerList'])->name('GetOwnerPlayerList');
    Route::get('owner-cash-in/{id}', [OwnerController::class, 'getCashIn'])->name('owner.getCashIn');
    Route::post('owner-cash-in/{id}', [OwnerController::class, 'makeCashIn'])->name('owner.makeCashIn');
    Route::get('mastownerer/cash-out/{id}', [OwnerController::class, 'getCashOut'])->name('owner.getCashOut');
    Route::post('owner/cash-out/update/{id}', [OwnerController::class, 'makeCashOut'])
        ->name('owner.makeCashOut');
    Route::put('owner/{id}/ban', [OwnerController::class, 'banOwner'])->name('owner.ban');
    Route::get('owner-changepassword/{id}', [OwnerController::class, 'getChangePassword'])->name('owner.getChangePassword');
    Route::post('owner-changepassword/{id}', [OwnerController::class, 'makeChangePassword'])->name('owner.makeChangePassword');

    Route::get('super-cash-in/{id}', [SuperController::class, 'getCashIn'])->name('super.getCashIn');
    Route::post('super-cash-in/{id}', [SuperController::class, 'makeCashIn'])->name('super.makeCashIn');
    Route::get('super/cash-out/{id}', [SuperController::class, 'getCashOut'])->name('super.getCashOut');
    Route::post('super/cash-out/update/{id}', [SuperController::class, 'makeCashOut'])
        ->name('super.makeCashOut');
    Route::put('super/{id}/ban', [SuperController::class, 'banSuper'])->name('super.ban');
    Route::get('super-changepassword/{id}', [SuperController::class, 'getChangePassword'])->name('super.getChangePassword');
    Route::post('super-changepassword/{id}', [SuperController::class, 'makeChangePassword'])->name('super.makeChangePassword');

    Route::get('senior-cash-in/{id}', [SeniorController::class, 'getCashIn'])->name('senior.getCashIn');
    Route::post('senior-cash-in/{id}', [SeniorController::class, 'makeCashIn'])->name('senior.makeCashIn');
    Route::get('senior/cash-out/{id}', [SeniorController::class, 'getCashOut'])->name('senior.getCashOut');
    Route::post('senior/cash-out/update/{id}', [SeniorController::class, 'makeCashOut'])
        ->name('senior.makeCashOut');
    Route::put('senior/{id}/ban', [SeniorController::class, 'banSenior'])->name('senior.ban');
    Route::get('senior-changepassword/{id}', [SeniorController::class, 'getChangePassword'])->name('senior.getChangePassword');
    Route::post('senior-changepassword/{id}', [SeniorController::class, 'makeChangePassword'])->name('senior.makeChangePassword');

    Route::get('master-player-list', [MasterController::class, 'MasterPlayerList'])->name('GetMasterPlayerList');
    Route::get('master-cash-in/{id}', [MasterController::class, 'getCashIn'])->name('master.getCashIn');
    Route::post('master-cash-in/{id}', [MasterController::class, 'makeCashIn'])->name('master.makeCashIn');
    Route::get('master/cash-out/{id}', [MasterController::class, 'getCashOut'])->name('master.getCashOut');
    Route::post('master/cash-out/update/{id}', [MasterController::class, 'makeCashOut'])
        ->name('master.makeCashOut');
    Route::put('master/{id}/ban', [MasterController::class, 'banMaster'])->name('master.ban');
    Route::get('master-changepassword/{id}', [MasterController::class, 'getChangePassword'])->name('master.getChangePassword');
    Route::post('master-changepassword/{id}', [MasterController::class, 'makeChangePassword'])->name('master.makeChangePassword');

    Route::get('withdraw', [WithDrawRequestController::class, 'index'])->name('agent.withdraw');
    Route::post('withdraw/{withdraw}', [WithDrawRequestController::class, 'statusChangeIndex'])->name('agent.withdrawStatusUpdate');
    Route::post('withdraw/reject/{withdraw}', [WithDrawRequestController::class, 'statusChangeReject'])->name('agent.withdrawStatusreject');

    Route::get('deposit', [DepositRequestController::class, 'index'])->name('agent.deposit');
    Route::get('deposit/{deposit}', [DepositRequestController::class, 'view'])->name('agent.depositView');
    Route::post('deposit/{deposit}', [DepositRequestController::class, 'statusChangeIndex'])->name('agent.depositStatusUpdate');
    Route::post('deposit/reject/{deposit}', [DepositRequestController::class, 'statusChangeReject'])->name('agent.depositStatusreject');

    Route::get('transer-log', [TransferLogController::class, 'index'])->name('transferLog');
    Route::get('transferlog/{id}', [TransferLogController::class, 'transferLog'])->name('transferLogDetail');

    Route::group(['prefix' => 'report'], function () {
        Route::get('ponewine', [ReportController::class, 'ponewine'])->name('report.ponewine');
        Route::get('ponewine-detail/{id}', [ReportController::class, 'detail'])->name('report.ponewineDetail');
        Route::get('report', [ReportController::class, 'index'])->name('report.index');
        Route::get('reports/details/{player_id}', [ReportController::class, 'getReportDetails'])->name('reports.details');
        Route::get('reports/player/{player_id}', [ReportController::class, 'getPlayer'])->name('reports.player.index');

    });

    Route::get('/owner-report/{id}', [OwnerController::class, 'ownerReportIndex'])->name('owner.report');
    Route::get('/super-report/{id}', [SuperController::class, 'superReportIndex'])->name('super.report');
    Route::get('/senior-report/{id}', [SeniorController::class, 'seniorReportIndex'])->name('senior.report');
    Route::get('/master-report/{id}', [MasterController::class, 'MasterReportIndex'])->name('master.report');
    Route::get('/agent-report/{id}', [AgentController::class, 'agentReportIndex'])->name('agent.report');

    // report backup
     Route::get('/resultsdata', [ResultArchiveController::class, 'getAllResults'])->name('backup_results.index');
     Route::post('/archive-results', [ResultArchiveController::class, 'archiveResults'])->name('archive.results');

     Route::post('/generate-daily-summaries', [DailySummaryController::class, 'generateSummaries'])->name('generate_daily_sammary');

     Route::get('/daily-summaries', [DailySummaryController::class, 'index'])
    ->name('daily_summaries.index');

    Route::get('/seamless-transactions', [DailySummaryController::class, 'SeamlessTransactionIndex'])
        ->name('seamless_transactions.index');
    Route::post('/seamless-transactions/delete', [DailySummaryController::class, 'deleteByDateRange'])
        ->name('seamless_transactions.delete');

    Route::get('/transaction-cleanup', [DailySummaryController::class, 'TransactionCleanupIndex'])
        ->name('transaction_cleanup.index');
    Route::post('/transaction-cleanup/delete', [DailySummaryController::class, 'delete'])
        ->name('transaction_cleanup.delete');

});
