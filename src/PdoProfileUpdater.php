<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\UpdateProfileException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This Callable updates a user's profile.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoProfileUpdater;
 *
 *     $pdo    = new PDO( ... );
 *     $logger = new Monolog();
 *     $table  = 'users';
 *     $user   = 42;
 *
 *     $updater = new PdoProfileUpdater( $pdo, $logger, $table);
 *     $result = $updater( $user, [
 *         'first_name'   => 'John',
 *         'last_name'    => 'Doe',
 *         'display_name' => 'John Doe',
 *         'email'        => 'john@test.com',
 *         'login_name'   => 'john@test.com'
 *     ]);
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoProfileUpdater
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
     * @param PDO             $pdo         PDO instance
     * @param LoggerInterface $logger      Optional: PSR-3 Logger
     * @param string          $table       Optional: Database table name
     */
	public function __construct( \PDO $pdo, LoggerInterface $logger = null, $table = null )
    {
        // Setup
		$this->logger = $logger ?: new NullLogger;
        $this->table  = $table  ?: $this->table;


        // Prepare business
        $sql = "UPDATE {$this->table} SET
        user_first_name   = :first_name,
        user_last_name    = :last_name,
        user_display_name = :display_name,
        user_email        = :email,
        user_login_name   = :login_name
        WHERE id = :id
        LIMIT 1";

        // Perform update
        $this->stmt = $pdo->prepare( $sql );


	}

    /**
     * @param  int   $user_id   User ID
     * @param  array $user_data User data
     *
     * @return bool  PDOStatement execution result
     */
    public function __invoke( $user_id, array $user_data )
    {

        // Setup
        $required_fields = [
            'first_name',
            'last_name',
            'display_name',
            'email',
            'login'
        ];

        // Check data
        foreach($required_fields as $rf):
            if (!array_key_exists($rf, $user_data)) :

                $message = "Missing field '$rf' in user data";
                $this->logger->error( $message );
                throw new UpdateProfileException( $message );

            endif;
        endforeach;


        // Perform
        $result = $this->stmt->execute([
            'id' => $user_id,
            'first_name'   => $user_data[ "first_name" ],
            'last_name'    => $user_data[ "last_name" ],
            'display_name' => $user_data[ "display_name" ],
            'email'        => $user_data[ "email" ],
            'login_name'   => $user_data[ "login" ]
        ]);


        // Evaluate
        $loginfo = [
            'result'       => $result,
            'display_name' => $user_data[ "display_name" ]
        ];

        if ($result):
            $this->logger->info("Successfully updated user profile.", $loginfo);
            return $result;
        endif;

        $this->logger->error("Could not update user profile?!", $loginfo);
        throw new UpdateProfileException;
    }

}
