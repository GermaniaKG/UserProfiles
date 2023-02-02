<?php
namespace Germania\UserProfiles;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;

/**
 * This Callable checks if given username and password match a stored login name and password combination,
 * using a custom verifier function.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoNewCredentialsValidator;
 *
 *     $pdo      = new PDO( ... );
 *     $verifier = function() { return false; };
 *     $logger   = new Monolog();
 *     $users_table    = 'users';
 *
 *     $checker = new PdoNewCredentialsValidator( $pdo, $verifier, $logger, $users_table);
 *     $data = $checker( 'joehndoe@test.com', 'take_this_secret' );
 *
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoNewCredentialsValidator
{

    /**
     * Database users_table
     * @var string
     */
    public $users_table = 'users';

    /**
     * Database users_roles_table
     * @var string|null
     */
    public $users_roles_table;

    /**
     * Database roles_table
     * @var string|null
     */
    public $roles_table;

    /**
     * @var PDOStatement
     */
    public $stmt;

    /**
     * @var Callable
     */
    public $password_verifier;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var UuidFactory
     */
    public $uuid_factory;


    /**
     * @param PDO             $pdo                    PDO instance
     * @param Callable        $password_verifier      Callable password verifier that accepts password and password hash
     * @param LoggerInterface $logger                 Optional: PSR-3 Logger
     * @param string          $users_table            Optional: Database users_table name, default: `users`
     * @param string          $users_roles_table      Optional: Name of users/roles relations table.
     * @param string          $roles_table            Optional: Name of roles table
     */
    public function __construct( \PDO $pdo, Callable $password_verifier, LoggerInterface $logger = null, string $users_table = null, string $users_roles_table = null, string $roles_table = null)
    {
        $this->password_verifier = $password_verifier;
        $this->logger            = $logger ?: new NullLogger;
        $this->users_table       = $users_table  ?: $this->users_table;
        $this->users_roles_table = $users_roles_table;
        $this->roles_table       = $roles_table;
        $this->uuid_factory      = new UuidFactory;


        // Prepare business

        // Just the user data
        if (empty($this->users_roles_table) or empty($this->roles_table)) {
            $sql = "SELECT
            id,
            LOWER(HEX(uuid)) as uuid,
            api_key,
            password
            FROM {$this->users_table}
            WHERE user_login_name = :login_name
            LIMIT 1";
        }

        // Or, additionally, with roles.
        else if (!empty($this->users_roles_table) and !empty($this->roles_table)) {

            $sql = "SELECT
            U.id,
            LOWER(HEX(U.uuid)) as uuid,
            U.api_key,
            U.password,

            GROUP_CONCAT(DISTINCT R.usergroup_short_name SEPARATOR ',') AS roles
            FROM {$this->users_table} U

            -- Zuordnung der Rollen (many-to-many)
            LEFT JOIN {$this->users_roles_table} UR
            ON U.id = UR.client_id
            LEFT JOIN {$this->roles_table} R
            ON R.id = UR.role_id

            WHERE U.user_login_name = :login_name
            LIMIT 1";
        }
        else {
            throw new \UnexpectedValueException("Expected roles table AND users-roles table as well.");
        }

        // Store for later use
        $this->stmt = $pdo->prepare( $sql );

    }



    /**
     * @param  string $login_name
     * @param  string $password
     *
     * @return mixed  Array with User's ID, UUID, and API key, or FALSE on wrong credentials
     */
    public function __invoke( $login_name, $password )
    {

        // Perform
        $this->stmt->execute([
            'login_name' => $login_name
        ]);


        // If user not found...
        $found_user = $this->stmt->fetch( \PDO::FETCH_ASSOC );
        if (!$found_user):
            $this->logger->warning("User login name not found", [
                'user_name' => $login_name
            ]);
            return false;
        endif;


        // If password is wrong...
        $verifier = $this->password_verifier;
        if(!$verifier( $password, $found_user['password'])) :

            $this->logger->warning("Wrong password", [
                'user_name' => $login_name
            ]);
            return false;
        endif;

        // Return found user data
        $uuid_factory = $this->uuid_factory;
        $uuid = $uuid_factory->fromString( $found_user['uuid'] );

        $result = [
            'id'      => $found_user['id'],
            'uuid'    => $uuid,
            'api_key' => $found_user['api_key']
        ];

        if (!empty($found_user['roles'])) {
            $result['roles'] = explode(",", $found_user['roles']);
        }

        return $result;

    }



}
