<?php
namespace tests;

use Germania\UserProfiles\PdoPasswordSetter;
use Germania\UserProfiles\Exceptions\SetPasswordException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class PdoPasswordSetterTest extends PdoTestcase
{

    public $logger;

    public function setUp() : void
    {
        $this->logger = new NullLogger;
    }

    /**
     * @dataProvider provideData
     */
    public function testNormalUsage( $user_id, $password )
    {
        $expected_result = true;
        $stmt_mock = $this->createPdoStatementMock( $expected_result );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $hash = function( $raw ) { return $raw; };

        $sut = new PdoPasswordSetter( $pdo_mock, $hash, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut($user_id, $password);

        $this->assertEquals( $expected_result, $result );
    }


    /**
     * @dataProvider provideData
     */
    public function testErrorUsage( $user_id, $password )
    {
        $stmt_mock = $this->createPdoStatementMock( false );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $hash = function( $raw ) { return $raw; };

        $sut = new PdoPasswordSetter( $pdo_mock, $hash, $this->logger);

        $this->expectException( SetPasswordException::class );
        $result = $sut($user_id, $password);

    }




    public function provideData()
    {
        return array(
            [ 1,"foo"]
        );
    }


}
