<?php

use PHPUnit\Framework\TestCase;
use Stillat\Primitives\Parser;

class ParserTest extends TestCase
{

    protected $parser;

    protected $strings = [
        'test',
        'with spaces',
        'with \'\\ \ escape'
    ];

    protected $numbers = [
        -1,
        0,
        32.32,
        0b11111111,
        0123
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->parser = new Parser();
    }

    public function test_it_parses_simple_strings()
    {
        foreach ($this->strings as $string) {
            $this->assertSame($string, $this->parser->parseString('"'.$string.'"')[0]);
        }
    }

    public function test_it_parses_numbers()
    {
        foreach ($this->numbers as $number) {
            $this->assertSame($number, $this->parser->parseString("{$number}")[0]);
        }
    }

    public function test_it_parses_simple_arrays()
    {
        $input = '[1, 2, 3, 4, "five"]';
        $this->assertSame([
            1, 2, 3, 4, "five"
        ], $this->parser->parseString($input)[0]);
    }

    public function test_it_parses_associative_arrays()
    {
        $input = '[1 => "one", 2 => "two", "three" => 3]';
        $this->assertSame([
            1 => "one",
            2 => "two",
            "three" => 3
        ], $this->parser->parseString($input)[0]);
    }

    public function test_it_parses_php_constants()
    {
        $this->assertSame(M_PI, $this->parser->parseString('M_PI')[0]);
        $this->assertSame(M_EULER, $this->parser->parseString('M_EULER')[0]);
    }

    public function test_it_parses_nested_arrays()
    {
        $input = '[[[[["one" => [1,2,3,4]]]]]]';

        $this->assertSame([[[[["one" => [
            1,2,3,4
        ]]]]]], $this->parser->parseString($input)[0]);
    }

    public function test_everything_together()
    {
        $input = '["foo", "bar"], 3';

        $this->assertSame([['foo', 'bar'], 3], $this->parser->parseString($input));
    }

    public function test_constants()
    {
        $this->assertSame([null, true, false], $this->parser->parseString('[null, true, false]')[0]);
    }

    public function test_extract_method()
    {
        $this->assertNull($this->parser->parseMethod('invalidSyntax"one,two")'));

        $this->assertSame([
            'randomElement',
            [
                ['foo', 'bar'],
                3
            ]
        ], $this->parser->parseMethod('randomElement(["foo", "bar"], 3)'));
    }

}