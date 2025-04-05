<?php

namespace App\Enums;

enum UserType: int
{
    case Senior = 10;
    case Owner = 20;
    case Agent = 30;
    case Player = 40;
    case SystemWallet = 50;
    case subAgent = 60;

    public static function usernameLength(UserType $type)
    {
        return match ($type) {
            self::Senior => 1,
            self::Owner => 2,
            self::Agent => 3,
            self::Player => 4,
            self::SystemWallet => 5,
            self::subAgent => 6
        };
    }

    public static function childUserType(UserType $type)
    {
        return match ($type) {
            self::Senior => self::Owner,
            self::Owner => self::Agent,
            self::Agent => self::subAgent,
            self::Agent => self::Player,
            self::Player => self::Player
        };
    }
}
