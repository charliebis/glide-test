<?php


class Validator
{

    public static function alphaNumeric($str)
    {
        return preg_match('/^[a-zA-Z0-9]+$/i', $str);
    }


    public static function integer($str)
    {
        return preg_match('/^\d+?$/i', $str);
    }


    public static function routeFormat($str)
    {
        return preg_match('%^\/([\w\d_-]+?\/)+$%i', $str);
    }


    public static function urlFormat($str)
    {
        return preg_match('/^(https?|ftp|file):\\/\\/[-A-Z0-9+&@#\\/%?=~_|$!:,.;]*[A-Z0-9+&@#\\/%=~_|$]$/i', $str);
    }


    public static function filePathFormat($str)
    {
        return preg_match('%^\/(?:(?:[\w\d_-]+?\/)+)?(?:[\w\d_-]+?\.[\w\d]+?)?$%i', $str);
    }
}