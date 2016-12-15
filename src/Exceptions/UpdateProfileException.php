<?php
namespace Germania\UserProfiles\Exceptions;

class UpdateProfileException extends UserProfileException implements UserProfileExceptionInterface
{
    protected $message = "Could not update the user's profile.";
}
