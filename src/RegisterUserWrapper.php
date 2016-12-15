<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\RegisterUserException;
use Germania\UserProfiles\Exceptions\LoginNameNotAvailableException;
use Germania\UserProfiles\Exceptions\InsertUserException;
use Germania\UserProfiles\Exceptions\SetPasswordException;
use Germania\UserProfiles\Exceptions\SetApiKeyException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


/**
 * Wraps all required tasks for registering a new user, without marking him `active` or assigning user roles.
 */
class RegisterUserWrapper
{

    /**
     * @var Callable
     */
    public $check_username;

    /**
     * @var Callable
     */
    public $insert_user;

    /**
     * @var Callable
     */
    public $set_password;

    /**
     * @var Callable
     */
    public $set_api_key;

    /**
     * @var Callable
     */
    public $assign_role;


    public $users_table       = 'users';


    /**
     * @param PDO              $pdo
     * @param Callable         $hasher
     * @param Callable         $randomizer
     * @param string           $users_table
     * @param LoggerInterface  $logger Optional: PSR-3 Logger
     */
    public function __construct(\PDO $pdo, Callable $hasher, Callable $randomizer, $users_table = null, LoggerInterface $logger = null)
    {
        $this->logger          = $logger ?: new NullLogger;
        $this->users_table     = $users_table       ?: $this->users_table;

        $this->check_username  = new PdoUsernameChecker(  $pdo, $logger,     $users_table);
        $this->insert_user     = new PdoInsertNewUser(    $pdo, $logger,     $users_table);
        $this->set_password    = new PdoPasswordSetter(   $pdo, $hasher,     $logger, $users_table);
        $this->set_api_key     = new PdoApiKeySetter(  $pdo, $randomizer, $logger, $users_table);
    }


    /**
     * Performs common actions to register a new user, including:
     *
     * - Check if a username is available
     * - Insert new User
     * - Set Password for new user ID
     * - Set API key for new user ID
     *
     * The user_data array must contain these elements:
     *
     * - first_name
     * - last_name
     * - display_name
     * - email
     * - login
     * - password
     *
     * @param array $user_data
     * @return int New User ID
     * @throws UserProfileExceptionInterface
     */
    public function __invoke( array $user_data )
    {
        if (!array_key_exists("login", $user_data)
        or  !array_key_exists("password", $user_data)):
            throw new RegisterUserException("Missing fields 'login' and/or 'password'");
        endif;

        //
        // 1. Is login name available?
        //    (may throw LoginNameNotAvailableException)
        //
        $check_username = $this->check_username;
        $check_username( $user_data['login'] );


        //
        // 2. Insert new User
        //    (may throw InsertUserException)
        //
        $insert_user = $this->insert_user;
        $new_user_id = $insert_user( $user_data );


        //
        // 3. Set password
        //    (may throw SetPasswordException)
        //
        $set_password = $this->set_password;
        $set_password( $new_user_id, $user_data[ 'password' ] );


        //
        // 4. Create API Key
        //    (may throw SetApiKeyException)
        //
        $set_api_key = $this->set_api_key;
        $set_api_key( $new_user_id );


        //
        // Alles klar bis hier?
        //
        return $new_user_id;

    }
}
