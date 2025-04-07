<?php

namespace App\Http\Controllers\Api\V1\Slot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Slot\LaunchGameRequest;
use App\Services\Webhook\LaunchGameService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class LaunchGameController extends Controller
{
    use HttpResponses;

    protected $gameService;
    //private const WEB_PLAT_FORM = 0;

    private const ENG_LANGUAGE_CODE = 'en-us';

    // Inject the GameService into the controller
    public function __construct(LaunchGameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function LaunchGame(LaunchGameRequest $request)
    {
        $response = $this->gameService->gameLogin(
            $request->game_code,
            $request->input('launch_demo', false)
        );

        return $this->success('Launch Game success', $response);
    }
}
