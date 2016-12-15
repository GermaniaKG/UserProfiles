<?php
namespace Germania\UserProfiles\Exceptions;

class SetPasswordException extends \Exception implements UserProfileExceptionInterface
{
    protected $message = "Could not set the user's password.";
}
