<?php
namespace Germania\UserProfiles\Exceptions;

class InsertUserException extends \Exception implements UserProfileExceptionInterface
{
    protected $message = "Could not add this user to the database.";
}
