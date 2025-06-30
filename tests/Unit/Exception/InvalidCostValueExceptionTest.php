<?php

namespace PrepaidCardBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use PrepaidCardBundle\Exception\InvalidCostValueException;

class InvalidCostValueExceptionTest extends TestCase
{
    public function testExceptionInstance(): void
    {
        $exception = new InvalidCostValueException('Invalid cost value');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(InvalidCostValueException::class, $exception);
        $this->assertEquals('Invalid cost value', $exception->getMessage());
    }
    
    public function testExceptionWithCode(): void
    {
        $exception = new InvalidCostValueException('Test message', 100);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(100, $exception->getCode());
    }
    
    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidCostValueException('Test message', 0, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}