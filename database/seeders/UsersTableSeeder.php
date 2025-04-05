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
        $seniorOwner = $this->createUser(UserType::SeniorOwner, 'Senior Owner', 'ponewine', '09123456789');
        (new WalletService)->deposit($seniorOwner, 500_000_00000000, TransactionName::CapitalDeposit);

        $owner = $this->createUser(UserType::Owner, 'Owner', 'O3456454', '09123456789', $seniorOwner->id);
        (new WalletService)->transfer($seniorOwner, $owner, 5 * 100_0000000, TransactionName::CreditTransfer);

        $super = $this->createUser(UserType::Super, 'Super', 'Super123', '09123456789', $owner->id);
        (new WalletService)->transfer($owner, $super, 3 * 100_000000, TransactionName::CreditTransfer);

        $senior = $this->createUser(UserType::Senior, 'Senior', 'Senior123', '0956437865', $super->id);
        (new WalletService)->transfer($super, $senior, 2 * 100_000000, TransactionName::CreditTransfer);

        $master = $this->createUser(UserType::Master, 'Master', 'M3456454', '09876556665', $senior->id);
        (new WalletService)->transfer($senior, $master, 1 * 100_00000, TransactionName::CreditTransfer);

        $agent = $this->createUser(UserType::Agent, 'Agent 1', 'A898737', '09112345674', $master->id, 'vH6u5E9');
        (new WalletService)->transfer($master, $agent, 5 * 100_0000, TransactionName::CreditTransfer);

        $player = $this->createUser(UserType::Player, 'Player 1', 'Player001', '09111111111', $agent->id);
        (new WalletService)->transfer($agent, $player, 5 * 100_00, TransactionName::CreditTransfer);

        $systemWallet = $this->createUser(UserType::SystemWallet, 'SystemWallet', 'systemWallet', '09222222222');
        (new WalletService)->deposit($systemWallet, 500 * 100_0000, TransactionName::CapitalDeposit);

        $player1 = $this->createUser(UserType::Player, 'Player2', 'Player002', '09111111112', $agent->id);
        (new WalletService)->transfer($agent, $player1, 5 * 100_00, TransactionName::CreditTransfer);
        $player2 = $this->createUser(UserType::Player, 'Player3', 'Player003', '09111111113', $agent->id);
        (new WalletService)->transfer($agent, $player2, 5 * 100_00, TransactionName::CreditTransfer);
        $player3 = $this->createUser(UserType::Player, 'Player4', 'Player004', '09111111114', $agent->id);
        (new WalletService)->transfer($agent, $player3, 5 * 100_00, TransactionName::CreditTransfer);
        $player4 = $this->createUser(UserType::Player, 'Player5', 'Player005', '09111111115', $agent->id);
        (new WalletService)->transfer($agent, $player4, 5 * 100_000, TransactionName::CreditTransfer);
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
