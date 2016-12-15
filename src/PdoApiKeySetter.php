<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\SetApiKeyException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This Callable sets a new random API key for a user's ID.
 *
 * Example:
 *
 *     use Germania\UserProfiles\PdoApiKeySetter;
 *
 *     $pdo    = new PDO( ... );
 *     $rnd    = function() { return 'ABCDEF'; };
 *     $logger = new Monolog();
 *     $table  = 'users';
 *
 *     $setter = new PdoApiKeySetter( $pdo, $random_gen, $logger, $table);
 *     $result = $setter( 42 );
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoApiKeySetter
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
    public $random_gen;

    /**
     * @param PDO             $pdo         PDO instance
     * @param Callable        $random_gen  Callable Random Generator
     * @param LoggerInterface $logger      Optional: PSR-3 Logger
     * @param string          $table       Optional: Database table name
     */
	public function __construct( \PDO $pdo, Callable $random_gen, LoggerInterface $logger = null, $table = null )
	{

        // Prerequisites
        $this->table      = $table ?: $this->table;
        $this->random_gen = $random_gen;
        $this->logger     = $logger;

        // Prepare business
        $sql = "UPDATE {$this->table}
        SET api_key = :apikey
        WHERE id = :user_id
        LIMIT 1";

        // Store for later use
        $this->stmt = $pdo->prepare( $sql );
	}


    /**
     * @param  int  $user_id
     *
     * @return bool TRUE on success
     *
     * @throws SetApiKeyException if PDOStatement execution fails.
     */
    public function __invoke( $user_id )
    {
        // Calc values
        $randomgen = $this->random_gen;
        $apikey    = $randomgen();

        // Perform
        $result = $this->stmt->execute([
            'apikey'  => $apikey,
            'user_id' => $user_id
        ]);

        // Evaluate
        $loginfo = [
            'user_id'   => $user_id,
            'mb_strlen' => mb_strlen($apikey ),
            'result'    => $result
        ];

        if ($result):
            $this->logger->info("Successfully created API key.", $loginfo);
            return $result;
        endif;

        $this->logger->warning("Could not set API key?!", $loginfo);
        throw new SetApiKeyException;
    }
}
