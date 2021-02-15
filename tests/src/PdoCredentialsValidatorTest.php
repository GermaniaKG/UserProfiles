<?php
namespace tests;

use Germania\UserProfiles\PdoCredentialsValidator;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PdoCredentialsValidatorTest extends PdoTestcase
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
    public function testNormalUsage( $login, $pass, $fetch_result, $verifier_result, $expected_result )
    {
        $stmt = $this->prophesize(\PDOStatement::class);
        $stmt->execute( Argument::type('array') )->willReturn( true );
        $stmt->fetch( Argument::any() )->willReturn( $fetch_result );
        $stmt_mock = $stmt->reveal();

        $pdo_mock = $this->createPdoMock( $stmt_mock );

        $verifier = function($login, $pass) use ($verifier_result) {
            return $verifier_result;
        };

        $sut = new PdoCredentialsValidator( $pdo_mock, $verifier, $this->logger);
        $this->assertInstanceOf( \PDOStatement::class, $sut->stmt );

        $result = $sut( $login, $pass);

        $this->assertSame( $expected_result, $result );
    }




    public function provideData()
    {
        $user = (object) [
            'id' => -42,
            'password' => "hashedstuff"
        ];
        return array(
            //                        $login,  $pass,   $fetch_result, $verifier_result, $expected_result
            "All OK data"       => [ "foo",    "bar",   $user,         true,             $user->id  ],
            "Verification fail" => [ "foo",    "bar",   $user,         false,            false ],
            "User unknown"      => [ "foo",    "bar",   null,          false,            false ]
        );
    }



}
