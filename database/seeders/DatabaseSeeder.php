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
            PPlaySlotSeeder::class,
            //PragmaticPlaySeeder::class,
            PGSoftGameListSeeder::class,
            Live22SMTablesSeeder::class,
            JiliTablesSeeder::class,
            JokerGameListSeeder::class,
            CQ9GameListTableSeeder::class,
            AsiaGamingTablesSeeder::class,
            CQ9FishingTablesSeeder::class,
            EvolutionGamingTableSeeder::class,
            HotGameTablesSeeder::class,
            SexyGamingSeeder::class,
            RealTimeGamingSeeder::class,
            YggdrasilSeeder::class,
            JDBTablesSeeder::class,
            KAGamingTablesSeeder::class,
            SpadeGamingTablesSeeder::class,
            SpadeGamingFishingTablesSeeder::class,
            PlayStarTablesSeeder::class,
            PlayStarFishingTablesSeeder::class,
            HabaneroGamingTablesSeeder::class,
            MrSlottyTablesSeeder::class,
            TrueLabTablesSeeder::class,
            BGamingTablesSeeder::class,
            WazdanTablesSeeder::class,
            FaziTablesSeeder::class,
            NetGameTablesSeeder::class,
            RedRakeTablesSeeder::class,
            BoongoTablesSeeder::class,
            SkywindTablesSeeder::class,
            SkywindCasinoTablesSeeder::class,
            AdvantPlayTablesSeeder::class,
            GENESISTablesSeeder::class,
            SimplePlayTablesSeeder::class,
            FuntaGamingTablesSeeder::class,
            FelixGamingTablesSeeder::class,
            SmartSoftTablesSeeder::class,
            ZeusPlayTablesSeeder::class,
            NetentTablesSeeder::class,
            RedTigerTablesSeeder::class,
            GamingWorldTablesSeeder::class,
            YesGetRichTablesSeeder::class,
            ContactTypeSeeder::class,
        ]);
    }
}
