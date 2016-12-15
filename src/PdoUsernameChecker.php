<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\LoginNameNotAvailableException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Checks if a given username is available,
 * i.e. if it is not already in use.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoUsernameChecker;
 *
 *     $pdo    = new PDO( ... );
 *     $logger = new Monolog();
 *     $table  = 'users';
 *
 *     $checker = new PdoUsernameChecker( $pdo, $logger, $table);
 *     $result = $checker( 'johndoe_224' );
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoUsernameChecker
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
     * @param PDO             $pdo    PDO instance
     * @param LoggerInterface $logger Optional: PSR-3 Logger
     * @param string          $table  Optional: Database table name
     */
    public function __construct( \PDO $pdo, LoggerInterface $logger = null, $table = null)
    {
        // Setup
        $this->logger = $logger ?: new NullLogger;
        $this->table  = $table  ?: $this->table;

        // Prepare business
        $sql = "SELECT id
        FROM {$this->table}
        WHERE user_login_name = :username
        LIMIT 1";

        $this->stmt = $pdo->prepare( $sql );

    }


    /**
     * @param  string $user_name
     * @return bool   TRUE if username is available
     *
     * @throws LoginNameNotAvailableException if username is *not* available
     */
    public function __invoke( $user_name )
    {
        $bool = $this->stmt->execute([
          'username' => $user_name
        ]);

        $available = !$this->stmt->fetchColumn();

        // Evaluate
        $loginfo = [
            'user_name' => $user_name,
            'result'    => $available
        ];

        if ($available):
            $this->logger->info("Login name chosen is available.", $loginfo );
            return $available;
        endif;

        $this->logger->warning("Login name chosen not available!?", $loginfo);
        throw new LoginNameNotAvailableException;
    }



}
