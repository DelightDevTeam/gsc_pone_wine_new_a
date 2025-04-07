<?php

namespace Database\Seeders;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $senior = $this->createUser(UserType::Senior, 'Senior', 'slotmaker', '09123456789');
        (new WalletService)->deposit($senior, 500_000_000, TransactionName::CapitalDeposit);

        $owner = $this->createUser(UserType::Owner, 'Owner', 'O3456454', '09876556665', $senior->id, 'vH4Hu34');
        (new WalletService)->transfer($senior, $owner, 0.00, TransactionName::CreditTransfer);

        $agent = $this->createUser(UserType::Agent, 'Agent 1', 'A898737', '09112345674', $owner->id, 'vH6u5E9');
        (new WalletService)->transfer($owner, $agent, 0.00, TransactionName::CreditTransfer);

        $player = $this->createUser(UserType::Player, 'Player 1', 'Player001', '09111111111', $agent->id);
        (new WalletService)->transfer($agent, $player, 0.00, TransactionName::CreditTransfer);

        $systemWallet = $this->createUser(UserType::SystemWallet, 'SystemWallet', 'systemWallet', '09222222222');
        (new WalletService)->deposit($systemWallet, 50 * 100_0000, TransactionName::CapitalDeposit);

    }

    private function createUser(UserType $type, $name, $user_name, $phone, $parent_id = null, $referral_code = null)
    {
        return User::create([
            'name' => $name,
            'user_name' => $user_name,
            'phone' => $phone,
            'password' => Hash::make('delightmyanmar'),
            'agent_id' => $parent_id,
            'status' => 1,
            'is_changed_password' => 1,
            'type' => $type->value,
            'referral_code' => $referral_code,
        ]);
    }
}
