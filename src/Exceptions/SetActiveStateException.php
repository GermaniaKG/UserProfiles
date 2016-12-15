<?php
namespace Germania\UserProfiles\Exceptions;

class SetActiveStateException extends \Exception implements UserProfileExceptionInterface
{
    protected $message = "Could not set the user's 'active' status.";
}
