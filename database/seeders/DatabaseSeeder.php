<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            PermissionRoleTableSeeder::class,
            PaymentTypeTableSeeder::class,
            UsersTableSeeder::class,
            RoleUserTableSeeder::class,
            BannerSeeder::class,
            BannerTextSeeder::class,
            BannerAdsSeeder::class,
            BankTableSeeder::class,
            GameTypeTableSeeder::class,
            GameProductSeeder::class,
            GameTypeProductTableSeeder::class,
            PragmaticPlaySlotSeeder::class,
            PragmaticPlayLiveCasinoSeeder::class,
            PGSoftGameListSeeder::class,
            JiliFishingSeeder::class,
            JILIGameTableSeeder::class,
            Live22GameTableSeeder::class,
            CQ9GameTableSeeder::class,
            UUSlotGameListSeeder::class, // 8
            MegaH5GameTableSeeder::class,
            EpicWinGameTableSeeder::class,
            YellowBatGameTableSeeder::class,
            EVOPLAYGameTableSeeder::class,
            FACHAIGameTableSeeder::class,
            BNGGameTableSeeder::class,
            YGRGameTableSeeder::class,
            FUNTAGameTableSeeder::class,
            SimplePlayGameTableSeeder::class,
            JDBGameTableSeeder::class,
            SAGamingSeeder::class,
            GPDSlotSeeder::class,
            GPDArcadeSeeder::class,
            GPDTableSeeder::class,
            GPDLotterySeeder::class,
            R88SlotSeeder::class,
            R88ArcadeSeeder::class,
            R88LotterySeeder::class,
            ContactTypeSeeder::class
        ]);
    }
}
