<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\GameType;
use App\Models\Admin\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameTypeProductController extends Controller
{
    public function index()
    {
        $gameTypes = GameType::with('products')->where('status', 1)
            ->get();

        return view('admin.game_type.index', compact('gameTypes'));
    }

    public function toggleStatus(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->toggleStatus()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'newStatus' => $product->status,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update status.',
        ], 500);
    }

    public function GameTypeindex()
    {
        $gameTypes = GameType::all();

        return view('admin.game_type.game_typeindex', compact('gameTypes'));
    }

    public function GametoggleStatus(Request $request, $productId)
    {
        $product = GameType::findOrFail($productId);

        if ($product->GameTypetoggleStatus()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'newStatus' => $product->status,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update status.',
        ], 500);
    }

    public function edit($gameTypeId, $productId)
    {
        $gameType = GameType::with([
            'products' => function ($query) use ($productId) {
                $query->where('products.id', $productId);
            },
        ])->where('id', $gameTypeId)->first();

        return view('admin.game_type.edit', compact('gameType', 'productId'));
    }

    public function update(Request $request, $gameTypeId, $productId)
    {
        $image = $request->file('image');
        $ext = $image->getClientOriginalExtension();
        $filename = uniqid('game_type').'.'.$ext;
        $image->move(public_path('assets/img/game_logo/'), $filename);

        DB::table('game_type_product')->where('game_type_id', $gameTypeId)->where('product_id', $productId)
            ->update(['image' => $filename]);

        return redirect()->route('admin.gametypes.index');
    }
}
