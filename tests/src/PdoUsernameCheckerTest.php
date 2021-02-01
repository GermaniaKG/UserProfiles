<?php
namespace tests;

use Germania\UserProfiles\PdoUsernameChecker;
use Germania\UserProfiles\Exceptions\LoginNameNotAvailableException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PdoUsernameCheckerTest extends PdoTestcase
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
    public function testNormalUsage( $user_id )
    {
        $expected_result = true;

        $stmt = $this->prophesize(\PDOStatement::class);
        $stmt->execute( Argument::type('array') )->willReturn( true );
        $stmt->fetchColumn( )->willReturn( false );
        $stmt_mock = $stmt->reveal();

        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoUsernameChecker( $pdo_mock, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut($user_id );

        $this->assertEquals( $expected_result, $result );
    }


    /**
     * @dataProvider provideData
     */
    public function testErrorUsage( $user_id )
    {
        $stmt = $this->prophesize(\PDOStatement::class);
        $stmt->execute( Argument::type('array') )->willReturn( true );
        $stmt->fetchColumn( )->willReturn( true );
        $stmt_mock = $stmt->reveal();

        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoUsernameChecker( $pdo_mock, $this->logger);

        $this->expectException( LoginNameNotAvailableException::class );
        $result = $sut($user_id);

    }


    public function provideData()
    {
        return array(
            [ 1 ]
        );
    }



}
