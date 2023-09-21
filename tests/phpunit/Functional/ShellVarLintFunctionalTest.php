<?php

namespace AlexSkrypnyk\ShellVarLint\Tests\Functional;

/**
 * Class ShellVarLintFunctionalTest.
 *
 * Functional tests for php-script.
 */
class ShellVarLintFunctionalTest extends ScriptFunctionalTestCase {

  /**
   * @covers ::main
   * @dataProvider dataProviderMain
   * @runInSeparateProcess
   * @group script
   */
  public function testMain(array|string $args, int $expected_code, string $expected_output): void {
    $result = $this->runScript($args);
    $this->assertEquals($expected_code, $result['code']);
    $this->assertArrayContainsString($expected_output, $result['output']);
  }

  /**
   * Data provider for testMain().
   */
  public static function dataProviderMain(): array {
    return [
      [
        '--help',
        static::EXIT_SUCCESS,
        'Check if shell script variables are wrapped in ${} and fix violations.',
      ],
      [
        '-help',
        static::EXIT_SUCCESS,
        'Check if shell script variables are wrapped in ${} and fix violations.',
      ],
      [
        '-h',
        static::EXIT_SUCCESS,
        'Check if shell script variables are wrapped in ${} and fix violations.',
      ],
      [
        '-?',
        static::EXIT_SUCCESS,
        'Check if shell script variables are wrapped in ${} and fix violations.',
      ],
      [
        [],
        static::EXIT_ERROR,
        'Please provide a file to check.',
      ],
      [
        ['somefile', 2, 3],
        static::EXIT_ERROR,
        'Unable to read file "somefile".',
      ],
    ];
  }

}
