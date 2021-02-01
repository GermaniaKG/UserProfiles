<?php
namespace tests;

use Germania\UserProfiles\PdoProfileUpdater;
use Germania\UserProfiles\Exceptions\UpdateProfileException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class PdoProfileUpdaterTest extends PdoTestcase
{

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
        $expected_result = true;
        $stmt_mock = $this->createPdoStatementMock( $expected_result );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoProfileUpdater( $pdo_mock, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut($user_id, $user_data);

        $this->assertEquals( $expected_result, $result );
    }


    /**
     * @dataProvider provideData
     */
    public function testErrorUsage( $user_id, $user_data )
    {
        $stmt_mock = $this->createPdoStatementMock( false );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoProfileUpdater( $pdo_mock, $this->logger);

        $this->expectException( UpdateProfileException::class );
        $result = $sut($user_id, $user_data);

    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testInvalidArgumentsUsage( $user_id, $user_data )
    {
        $stmt_mock = $this->createPdoStatementMock( true );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoProfileUpdater( $pdo_mock, $this->logger);

        $this->expectException( UpdateProfileException::class );
        $result = $sut($user_id, $user_data);

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
            [ 52, $user_data]
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
            [ 52,  $user_data_missing_fields]
        );
    }


}
