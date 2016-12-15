<?php
namespace Germania\UserProfiles\Exceptions;

class LoginNameNotAvailableException extends \Exception implements UserProfileExceptionInterface
{
    protected $message = "This username is not available";
}
