<?php

namespace App\Constants;

class AuthConstants
{
    // Success messages
    public const LOGIN = 'User login successfully';
    public const LOGOUT = 'User logout successfully';
    public const ME = 'Get auth detail successfully';
    public const REGISTER = 'User registers successfully';
    public const REFRESH = 'User token refresh was successful';

    // Error messages
    public const UNAUTHORIZED = 'Unauthorized, you are not authorized to access this resource';
    public const USER_NOT_FOUND = 'Make sure email and password are correct';
    public const TOKEN_NOT_PROVIDED = 'Token is not provided';
    public const TOKEN_INVALID = 'Token is invalid';
    public const TOKEN_EXPIRED = 'Token has expired';
}
