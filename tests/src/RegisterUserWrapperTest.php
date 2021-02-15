<?php
namespace tests;

use Germania\UserProfiles\RegisterUserWrapper;
use Germania\UserProfiles\Exceptions\UserProfileExceptionInterface;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class RegisterUserWrapperTest extends PdoTestcase
{
    use ProphecyTrait;

    public $logger;
    public $randomgen;
    public $hasher;

    public function setUp() : void
    {
        $this->logger = new NullLogger;
        $this->randomgen = function() { return "ABCDEF"; };
        $this->hasher = function( $raw ) { return "VWXYZ"; };
    }

    /**
     * @dataProvider provideData
     */
    public function testNormalUsage( $user_id, $user_data )
    {
        $stmt = $this->prophesize(\PDOStatement::class);
        $stmt->execute( Argument::type('array') )->willReturn( true );
        $stmt->fetchColumn( Argument::any() )->willReturn( false );
        $stmt_mock = $stmt->reveal();

        $pdo = $this->prophesize(\PDO::class);
        $pdo->prepare( Argument::type('string') )->willReturn( $stmt_mock );
        $pdo->lastInsertId( )->willReturn( $user_id );
        $pdo_mock = $pdo->reveal();

        $sut = new RegisterUserWrapper( $pdo_mock, $this->hasher, $this->randomgen, null, $this->logger);

        $result = $sut($user_data );

        $this->assertEquals( $user_id, $result );
    }


    /**
     * @dataProvider provideInvalidData
     */
    public function testErrorUsage( $user_id, $user_data )
    {
        $stmt_mock = $this->createPdoStatementMock( false );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new RegisterUserWrapper( $pdo_mock, $this->hasher, $this->randomgen, "table", $this->logger);

        $this->expectException( UserProfileExceptionInterface::class );
        $result = $sut($user_data);

    }

    public function provideData()
    {
        $user_data = [
            'first_name' => "foo",
            'last_name' => "foo",
            'display_name' => "foo",
            'email' => "foo",
            'login' => "foo",
            'password' => "bar"
        ];

        return array(
            "Complete User data" => [ 52, $user_data]
        );
    }


    public function provideInvalidData()
    {
        $user_data_missing_fields = [
            'first_name' => "foo",
            'last_name' => "foo",
            // 'display_name' => "foo",
            // 'email' => "foo",
            'login' => "foo"
        ];

        return array(
            "Incomplete User data" =>[ 52,  $user_data_missing_fields]
        );
    }

}
