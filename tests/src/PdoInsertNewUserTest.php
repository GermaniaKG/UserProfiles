<?php
namespace tests;

use Germania\UserProfiles\PdoInsertNewUser;
use Germania\UserProfiles\Exceptions\InsertUserException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PdoInsertNewUserTest extends PdoTestcase
{
    use ProphecyTrait;

    public $logger;

    public function setUp() : void
    {
        $this->logger = new NullLogger;
    }

    /**
     * @dataProvider provideData
     */
    public function testNormalUsage( $user_id, $user_data )
    {

        $stmt = $this->prophesize(\PDOStatement::class);
        $stmt->execute( Argument::type('array') )->willReturn( true );
        $stmt_mock = $stmt->reveal();


        $pdo = $this->prophesize(\PDO::class);
        $pdo->prepare( Argument::type('string') )->willReturn( $stmt_mock );
        $pdo->lastInsertId( )->willReturn( $user_id );
        $pdo_mock = $pdo->reveal();

        $sut = new PdoInsertNewUser( $pdo_mock, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut($user_data);

        $this->assertEquals( $user_id, $result );
    }


    /**
     * @dataProvider provideData
     */
    public function testErrorUsage( $user_id, $user_data )
    {
        $stmt_mock = $this->createPdoStatementMock( false );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoInsertNewUser( $pdo_mock, $this->logger);

        $this->expectException( InsertUserException::class );
        $result = $sut($user_data);

    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testInvalidArgumentsUsage( $user_id, $user_data )
    {
        $stmt_mock = $this->createPdoStatementMock( true );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoInsertNewUser( $pdo_mock, $this->logger);

        $this->expectException( InsertUserException::class );
        $result = $sut($user_data);

    }




    public function provideData()
    {
        $user_data = [
            'first_name' => "foo",
            'last_name' => "foo",
            'display_name' => "foo",
            'email' => "foo",
            'login' => "foo"
        ];

        return array(
            "User ID and complete data array" => [ 52, $user_data]
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
            "User ID and incomplete data array" => [ 52,  $user_data_missing_fields]
        );
    }


}
