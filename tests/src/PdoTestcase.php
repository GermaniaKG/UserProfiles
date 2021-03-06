<?php
namespace tests;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class PdoTestcase extends \PHPUnit\Framework\TestCase
{
    use ProphecyTrait;

    protected function createPdoStatementMock( $result )
    {
        $stmt = $this->prophesize(\PDOStatement::class);
        $stmt->execute( Argument::type('array') )->willReturn( $result );
        // $stmt->rowCount( )->willReturn( $row_count );
        return $stmt->reveal();
    }

    protected function createPdoMock( $stmt_mock )
    {
        $pdo = $this->prophesize(\PDO::class);
        $pdo->prepare( Argument::type('string') )->willReturn( $stmt_mock );
        return $pdo->reveal();
    }
}
