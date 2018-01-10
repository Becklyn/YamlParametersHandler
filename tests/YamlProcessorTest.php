<?php

namespace Becklyn\YamlParametersHandler\Tests;


use Becklyn\YamlParametersHandler\YamlProcessor;
use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;


class YamlProcessorTest extends TestCase
{
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp ()
    {
        $this->fixtures = __DIR__ . "/fixtures_current";
        $this->setUpFixtures();
    }


    /**
     * @inheritdoc
     */
    protected function tearDown ()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->fixtures);
    }


    protected function setUpFixtures ()
    {
        $fixtures = __DIR__ . "/fixtures";
        $filesystem = new Filesystem();
        $filesystem->remove($this->fixtures);
        $filesystem->mirror($fixtures, $this->fixtures);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissing ()
    {
        $io = $this->getMockBuilder(IOInterface::class)->getMock();
        $processor = new YamlProcessor($io);

        $processor->process("{$this->fixtures}/missing.yaml");
    }


    /**
     * Tests the process of generating the file
     */
    public function testProcess ()
    {
        $io = $this->getMockBuilder(IOInterface::class)->getMock();

        $io
            ->method("ask")
            ->willReturnArgument(1);

        $processor = new YamlProcessor($io);
        $processor->process("{$this->fixtures}/test.yaml");

        self::assertFileExists("{$this->fixtures}/test.yaml");

        $fileContent = \file_get_contents("{$this->fixtures}/test.yaml");
        self::assertContains("# This file is auto-generated", $fileContent);

        $yaml = new Parser();
        $in = $yaml->parseFile("{$this->fixtures}/test.yaml.dist");
        $out = $yaml->parseFile("{$this->fixtures}/test.yaml");
        self::assertEquals($in, $out);
    }


    /**
     * Tests, that the given input is actually used in the output
     */
    public function testAskInput ()
    {
        $this->setUpFixtures();
        $io = $this->getMockBuilder(IOInterface::class)->getMock();

        $io
            ->method("ask")
            ->willReturn("a");

        $processor = new YamlProcessor($io);
        $processor->process("{$this->fixtures}/test.yaml");

        $yaml = new Parser();
        $out = $yaml->parseFile("{$this->fixtures}/test.yaml");

        self::assertEquals(["parameters" => ["a" => "a"]], $out);
    }
}
