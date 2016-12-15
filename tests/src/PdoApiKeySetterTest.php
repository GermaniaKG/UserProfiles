<?php
namespace tests;

use Germania\UserProfiles\PdoApiKeySetter;
use Germania\UserProfiles\Exceptions\SetApiKeyException;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class PdoApiKeySetterTest extends PdoTestcase
{

    public $logger;
    public $randomgen;

    public function setUp()
    {
        $this->logger = new NullLogger;
        $this->randomgen = function() { return "ABCDEF"; };
    }

    /**
     * @dataProvider provideData
     */
    public function testNormalUsage( $user_id )
    {
        $expected_result = true;
        $stmt_mock = $this->createPdoStatementMock( $expected_result );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoApiKeySetter( $pdo_mock, $this->randomgen, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut($user_id );

        $this->assertEquals( $expected_result, $result );
    }


    /**
     * @dataProvider provideData
     */
    public function testErrorUsage( $user_id )
    {
        $stmt_mock = $this->createPdoStatementMock( false );
        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $sut = new PdoApiKeySetter( $pdo_mock, $this->randomgen, $this->logger);

        $this->expectException( SetApiKeyException::class );
        $result = $sut($user_id);

    }


    public function provideData()
    {
        return array(
            [ 1 ]
        );
    }



}
