<?php
namespace Germania\UserProfiles;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This Callable checks if given username and password match a stored login name and password combination,
 * using a custom verifier function.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoCredentialsValidator;
 *
 *     $pdo      = new PDO( ... );
 *     $verifier = function() { return false; };
 *     $logger   = new Monolog();
 *     $table    = 'users';
 *
 *     $checker = new PdoCredentialsValidator( $pdo, $verifier, $logger, $table);
 *     $result = $checker( 'joehndoe@test.com', 'take_this_secret' );
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoCredentialsValidator
{

    /**
     * Database table
     * @var string
     */
    public $table = 'users';

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
     * @param PDO             $pdo                PDO instance
     * @param Callable        $password_verifier  Callable password verifier that accepts password and password hash
     * @param LoggerInterface $logger             Optional: PSR-3 Logger
     * @param string          $table              Optional: Database table name
     */
    public function __construct( \PDO $pdo, Callable $password_verifier, LoggerInterface $logger = null, $table = null)
    {
        $this->password_verifier = $password_verifier;
        $this->logger            = $logger ?: new NullLogger;
        $this->table             = $table  ?: $this->table;


        // Prepare business
        $sql = "SELECT
        id,
        password
        FROM {$this->table}
        WHERE user_login_name = :login_name
        LIMIT 1";

        // Store for later use
        $this->stmt = $pdo->prepare( $sql );

    }



    /**
     * @param  string $login_name
     * @param  string $$password
     *
     * @return mixed  User ID as integer or FALSE on any failure
     */
    public function __invoke( $login_name, $password )
    {

        // Perform
        $this->stmt->execute([
            'login_name' => $login_name
        ]);


        // If user not found...
        $found_user = $this->stmt->fetch( \PDO::FETCH_OBJ );
        if (!$found_user):
            $this->logger->warning("User login name not found", [
                'user_name' => $login_name
            ]);
            return false;
        endif;


        // If password is wrong...
        $verifier = $this->password_verifier;
        if(!$verifier( $password, $found_user->password)) :

            $this->logger->warning("Wrong password", [
                'user_name' => $login_name
            ]);
            return false;
        endif;

        // Return found user ID
        return $found_user->id;

    }



}
