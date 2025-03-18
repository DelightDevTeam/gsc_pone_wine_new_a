<?php

namespace App\Enums;

enum UserType: int
{
    case SeniorOwner = 10;
    case Owner = 20;
    case Super = 30;
    case Senior =  40;
    case Master = 50;
    case Agent = 60;
    case Player = 70;
    case SystemWallet = 80;
    case subAgent = 90;

    public static function usernameLength(UserType $type)
    {
        return match ($type) {
            self::SeniorOwner => 1,
            self::Owner => 2,
            self::Super => 3,
            self::Senior => 4,
            self::Master => 5,
            self::Agent => 6,
            self::Player => 7,
            self::SystemWallet => 8,
            self::subAgent => 9
        };
    }

    public static function childUserType(UserType $type)
    {
        return match ($type) {
            self::SeniorOwner => self::Owner,
            self::Owner => self::Super,
            self::Super => self::Senior,
            self::Senior => self::Master,
            self::Master => self::Agent,
            self::Agent => self::subAgent,
            self::Agent => self::Player,
            self::Player => self::Player
        };
    }
}
