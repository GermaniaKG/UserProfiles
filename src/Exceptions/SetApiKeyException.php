<?php
namespace Germania\UserProfiles\Exceptions;

class SetApiKeyException extends \Exception implements UserProfileExceptionInterface
{
    protected $message = "Could not set the user's API key.";
}
