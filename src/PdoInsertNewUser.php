<?php
namespace Germania\UserProfiles;

use Germania\UserProfiles\Exceptions\InsertUserException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This Callable insert a new User and returns his new ID.
 *
 * Example:
 *
 *     <?php
 *     use Germania\UserProfiles\PdoInsertNewUser;
 *
 *     $pdo    = new PDO( ... );
 *     $logger = new Monolog();
 *     $table  = 'users';
 *
 *     $inserter = new PdoInsertNewUser( $pdo, $logger, $table);
 *     $new_id = $inserter([
 *         'first_name'   => 'John',
 *         'last_name'    => 'Doe',
 *         'display_name' => 'John Doe',
 *         'email'        => 'john@test.com',
 *         'login'        => 'john@test.com'
 *     ]);
 *     ?>
 *
 * @author  Carsten Witt <carstenwitt@germania-kg.de>
 */
class PdoInsertNewUser
{

    /**
     * Database table
     * @var string
     */
    public $table = 'users';

    /**
     * @var PDO
     */
    public $pdo;

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
        $this->pdo    = $pdo;
		$this->logger = $logger ?: new NullLogger;
        $this->table  = $table  ?: $this->table;

        // Prepare business
        $sql = "INSERT INTO {$this->table} (
        user_login_name,
        user_display_name,
        user_email,
        user_last_name,
        user_first_name,
        created
        ) VALUES (
        :user_login_name,
        :user_display_name,
        :user_email,
        :user_last_name,
        :user_first_name,
        :created
        )";

        // Prepare
        $this->stmt = $this->pdo->prepare( $sql );

	}




    /**
     * Inserts the user using the passed array. It must contain these elements:
     *
     * - first_name
     * - last_name
     * - display_name
     * - email
     * - login
     *
     * @param  array  $user_data User data
     *
     * @return int    New User ID
     *
     * @throws InsertUserException|UserProfileExceptionInterface if PDOStatement execution fails or missing required field.
     */
    public function __invoke( array $user_data )
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
                throw new InsertUserException( $message );

            endif;
        endforeach;


        // Perform
        $result = $this->stmt->execute([
            'user_login_name'   => $user_data[ "login" ],
            'user_display_name' => $user_data[ "display_name" ],
            'user_email'        => $user_data[ "email" ],
            'user_last_name'    => $user_data[ "last_name" ],
            'user_first_name'   => $user_data[ "first_name" ],
            'created' => date('Y-m-d H:i:s')
        ]) ? $this->pdo->lastInsertId() : null;


        // Evaluate
        $loginfo = [
            'new_user_id'       => $result,
            'user_display_name' => $user_data[ "display_name" ]
        ];
        if ($result):
            $this->logger->info("Successfully added new user.", $loginfo);
            return $result;
        endif;

        $this->logger->error("Could not add new user?!", $loginfo);
        throw new InsertUserException;

    }


}
