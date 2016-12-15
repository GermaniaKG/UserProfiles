<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\SetPasswordException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This Callable updates the password for the given user.
 * The password will be hashed by the hash function callable passed
 * to the constructor.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoPasswordSetter;
 *
 *     $pdo    = new PDO( ... );
 *     $hash   = function() { return 'ABCDEF'; };
 *     $logger = new Monolog();
 *     $table  = 'users';
 *
 *     $user = 42;
 *
 *     $setter = new PdoPasswordSetter( $pdo, $hash, $logger, $table);
 *     $result = $setter( 42, 'top_secret' );
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoPasswordSetter
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
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var Callable
     */
    public $hash_function;


    /**
     * @param PDO             $pdo           PDO instance
     * @param Callable        $hash_function Hash function callable
     * @param LoggerInterface $logger        Optional: PSR-3 Logger
     * @param string          $table         Optional: Database table name
     */
    public function __construct( \PDO $pdo, Callable $hash_function, LoggerInterface $logger = null, $table = null)
    {
        // Setup
        $this->hash_function = $hash_function;
        $this->logger        = $logger ?: new NullLogger;
        $this->table         = $table  ?: $this->table;

        // Prepare business
        $sql = "UPDATE {$this->table} SET
        password = :password
        WHERE id = :user_id
        LIMIT 1";

        // Store for later use
        $this->stmt = $pdo->prepare( $sql );

    }



    /**
     * @param  int    $user_id
     * @param  string $new_password
     *
     * @return bool   PDOStatement execution result
     *
     * @throws SetPasswordException if PDOStatement execution fails
     */
    public function __invoke( $user_id, $new_password )
    {

        // Calc values
        $hash_function = $this->hash_function;
        $password_hash = $hash_function( $new_password );


        // Perform
        $result = $this->stmt->execute([
            'password' => $password_hash,
            'user_id'  => $user_id
        ]);

        // Evaluate
        $loginfo = [
            'user_id'   => $user_id,
            'mb_strlen' => mb_strlen($password_hash ),
            'result'    => $result
        ];

        if ($result):
            $this->logger->info("Successfully set password.", $loginfo);
            return $result;
        endif;

        $this->logger->warning("Could not set password?!", $loginfo);
        throw new SetPasswordException;
    }



}
