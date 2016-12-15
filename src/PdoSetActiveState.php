<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\SetActiveStateException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


/**
 * This Callable sets the 'is_active' status for the given user.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoSetActiveState;
 *
 *     $pdo    = new PDO( ... );
 *     $hash   = function() { return 'ABCDEF'; };
 *     $logger = new Monolog();
 *     $table  = 'users';
 *
 *     $user = 42;
 *
 *     $setter = new PdoSetActiveState( $pdo, $logger, $table);
 *     $result = $setter( 42, PdoSetActiveState::ACTIVE );
 *     $result = $setter( 42, PdoSetActiveState::INACTIVE );
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoSetActiveState
{

    const ACTIVE   = 1;
    const INACTIVE = -1;

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
     * @param LoggerInterface $logger        Optional: PSR-3 Logger
     * @param string          $table         Optional: Database table name
     */
    public function __construct( \PDO $pdo,  LoggerInterface $logger = null, $table = null)
    {
        // Setup
        $this->logger        = $logger ?: new NullLogger;
        $this->table         = $table  ?: $this->table;

        // Prepare business
        $sql = "UPDATE {$this->table} SET
        is_active = :is_active
        WHERE id = :user_id
        LIMIT 1";

        // Store for later use
        $this->stmt = $pdo->prepare( $sql );

    }



    /**
     * @param  int    $user_id
     * @param  int    $status
     *
     * @return bool   TRUE on success
     *
     * @throws SetActiveStateException if PDOStatement execution fails
     */
    public function __invoke( $user_id, $is_active )
    {

        if (!filter_var($is_active, \FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException("Integer expected");
        }

        // Perform
        $result = $this->stmt->execute([
            'is_active' => $is_active,
            'user_id'   => $user_id
        ]);

        // Evaluate
        $loginfo = [
            'user_id'   => $user_id,
            'is_active' => $is_active,
            'result'    => $result
        ];

        if ($result):
            $this->logger->info("Successfully set 'is_active' state.", $loginfo);
            return $result;
        endif;

        $this->logger->warning("Could not set 'is_active' state?!", $loginfo);
        throw new SetActiveStateException;
    }



}
