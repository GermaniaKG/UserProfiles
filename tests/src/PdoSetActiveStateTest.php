<?php
namespace tests;

use Germania\UserProfiles\PdoSetActiveState;
use Germania\UserProfiles\Exceptions\SetActiveStateException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class PdoSetActiveStateTest extends PdoTestcase
{

    public $logger;

    public function setUp() : void
    {
        $this->logger = new NullLogger;
    }

    /**
     * @dataProvider provideData
     */
    public function testNormalUsage( $user_id, $status )
    {
        $expected_result = true;
        $stmt_mock = $this->createPdoStatementMock( $expected_result );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoSetActiveState( $pdo_mock, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut($user_id, $status);

        $this->assertEquals( $expected_result, $result );
    }


    /**
     * @dataProvider provideData
     */
    public function testErrorUsage( $user_id, $status )
    {
        $stmt_mock = $this->createPdoStatementMock( false );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoSetActiveState( $pdo_mock, $this->logger);

        $this->expectException( SetActiveStateException::class );
        $result = $sut($user_id, $status);

    }

    /**
     * @dataProvider provideInvalidData
     */
    public function testInvalidArgumentsUsage( $user_id, $invalid )
    {
        $stmt_mock = $this->createPdoStatementMock( true );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoSetActiveState( $pdo_mock, $this->logger);

        $this->expectException( \InvalidArgumentException::class );
        $result = $sut($user_id, $invalid);

    }




    public function provideData()
    {
        return array(
            [ 1, 1],
            [ 1, -1],
        );
    }

    public function provideInvalidData()
    {
        return array(
            [ 1, "notaninteger"],
            [ 1, 0.003]
        );
    }


}
