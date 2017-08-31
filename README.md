# Germania KG · UserProfiles

## Installation

```bash
$ composer require germania-kg/user-profiles
```

**MySQL users** may install the table *users* using `users.sql.txt` in `sql/` directory.



## A. Register a new User

Wraps all necessary tasks in a single Callable.  
For a detailed list of all single tasks, see chapters below.

0. Check if login name is available
0. Insert new User
0. Set user's password
0. Set user's API Key

*Please note that* just registration will not mark the new user as *active*. By default, he will be marked as *inactive* as desribed in MySQL table scheme. See section **B. Set users 'active' state** for details. 

If the registration fails for some reason, a **RegisterUserException** will be thrown.

```php
<?php
use Germania\UserProfiles\RegisterUserWrapper;
use Germania\UserProfiles\Exceptions\RegisterUserException;

$pdo    = new PDO( ... );
$hash   = function() { return ... ; };
$rnd    = function() { return ... ; };
$logger = new Monolog();

$users_table        = 'users';
$users_roles_table  = 'users_roles';

// Setup callable
$register = new RegisterUserWrapper( $pdo, $hash , $rnd, $users_table, $users_roles_table, $logger);


// User data
$user_data = [
    'first_name'   => 'John',
    'last_name'    => 'Doe',
    'display_name' => 'John Doe',
    'email'        => 'john@test.com',
    'login'        => 'john@test.com'
];

$roles = [1, 22];

// Store new user
try { 
	$user_id = $register( $user_data, $roles );
} 
catch (RegisterUserException $e) {
	echo "Failed: ", $e->getMessage();
}


```


### 1. Check if login name is available

Return TRUE if the given login name is available, FALSE otherwise.

```php
<?php
use Germania\UserProfiles\PdoUsernameChecker;
use Germania\UserProfiles\Exceptions\LoginNameNotAvailableException;

$pdo    = new PDO( ... );
$logger = new Monolog();
$table  = 'users';

try {
	$lookup = new PdoUsernameChecker( $pdo, $logger, $table);
	$available = $lookup( 'johndoe_224' );
}
catch (LoginNameNotAvailableException $e) {
	// Login name not available, someone else using it?
}
```




### 2. Insert new User

Throws a **UserProfileException** if required user data fields are missing.

```php
<?php
use Germania\UserProfiles\PdoInsertNewUser;
use Germania\UserProfiles\Exceptions\InsertUserException;

$pdo    = new PDO( ... );
$logger = new Monolog();
$table  = 'users';

try { 
	$inserter = new PdoInsertNewUser( $pdo, $logger, $table);
	$new_id = $inserter([
    	'first_name'   => 'John',
	    'last_name'    => 'Doe',
    	'display_name' => 'John Doe',
	    'email'        => 'john@test.com',
    	'login'        => 'john@test.com'
	]);
}
catch (InsertUserException $e) {
	// Hmmm. Could not insert new User?
	// Maybe some fields missing?
}
```



### 3. Set a Users' Password
```php
<?php
use Germania\UserProfiles\PdoPasswordSetter;
use Germania\UserProfiles\Exceptions\SetPasswordException;

$pdo    = new PDO( ... );
$hash   = function() { return 'ABCDEF'; };
$logger = new Monolog();
$table  = 'users';

$user = 42;

try {
	$setter = new PdoPasswordSetter( $pdo, $hash, $logger, $table);
	$result = $setter( 42, 'top_secret' );
}
catch (SetPasswordException $e) {
	// Could not change user's apssword?!
}
```


### 4. Set a User's API Key
```php
<?php
use Germania\UserProfiles\PdoApiKeySetter;
use Germania\UserProfiles\Exceptions\SetApiKeyException;

$pdo    = new PDO( ... );
$rnd    = function() { return 'ABCDEF'; };
$logger = new Monolog();
$table  = 'users';


try {
	$setter = new PdoApiKeySetter( $pdo, $random_gen, $logger, $table);
	$result = $setter( 42 );
}
catch (SetApiKeyException $e) {
	// Could not set new API key.
}

```




-------------------

## B. Set users 'active' state

```php
<?php
use Germania\UserProfiles\PdoSetActiveState;
use Germania\UserProfiles\Exceptions\SetActiveStateException;

$pdo    = new PDO( ... );
$hash   = function() { return 'ABCDEF'; };
$logger = new Monolog();
$table  = 'users';

$user = 42;

try {
	$setter = new PdoSetActiveState( $pdo, $logger, $table);
	$result = $setter( 42, PdoSetActiveState::ACTIVE );
	$result = $setter( 42, PdoSetActiveState::INACTIVE );
} 
catch (SetActiveStateException $e) {
	// Could not change active state?!
}
```

-------------------



## C. Update user profile

Throws a **UserProfileException** if required user data fields are missing.

```php
<?php
use Germania\UserProfiles\PdoProfileUpdater;
use Germania\UserProfiles\Exceptions\UpdateProfileException;

$pdo    = new PDO( ... );
$logger = new Monolog();
$table  = 'users';

$user   = 42;

try {
	$updater = new PdoProfileUpdater( $pdo, $logger, $table);
	$result = $updater( $user, [
	    'first_name'   => 'John',
	    'last_name'    => 'Doe',
	    'display_name' => 'John Doe',
	    'email'        => 'john@test.com',
	    'login_name'   => 'john@test.com'
	]);
}
catch (UpdateProfileException $e) {
	// Could not update the user's profile
}
```


-------------------


## D. Validate a user's credentials:

Check if login name and password match a stored user:

```php
<?php
use Germania\UserProfiles\PdoCredentialsValidator;

$pdo      = new PDO( ... );
$verifier = function() { return false; };
$logger   = new Monolog();
$table    = 'users';

$checker = new PdoCredentialsValidator( $pdo, $verifier, $logger, $table);
$result = $checker( 'joehndoe@test.com', 'take_this_secret' );

if ($result) {
	// valid
} else {
	// Password and/or Login name are invalid!
}
```

## Development and Testing

Develop using `develop` branch, using [Git Flow](https://github.com/nvie/gitflow).   
Go to proejct root and issue `phpunit`.

```bash
$ git clone git@github.com:GermaniaKG/UserProfiles.git user-profiles
$ cd user-profiles
$ cp phpunit.xml.dist phpunit.xml
$ phpunit
```

