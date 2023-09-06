<?php

namespace AlexSkrypnyk\ShellWrapVariables\Tests\Unit;

/**
 * Class ShellVarLintUnitTest.
 *
 * Unit tests for shell-var-lint.
 *
 * @group scripts
 */
class ShellVarLintUnitTest extends ScriptUnitTestBase {

  /**
   * Array of fixtures.
   *
   * @var string[]
   */
  protected static $fixtureFiles = [
    'valid' => 'wrapped.sh',
    'invalid' => 'unwrapped.sh',
  ];

  /**
   * {@inheritdoc}
   */
  protected $script = 'shell-var-lint';

  /**
   * Test main() method.
   *
   * @covers ::main
   * @covers ::print_help
   * @covers ::verbose
   * @dataProvider dataProviderMain
   */
  public function testMain($args, $expected_code, $expected_output) {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runScript($args, TRUE);
    $this->assertEquals($expected_code, $result['code']);
    $this->assertStringContainsString($expected_output, $result['output']);
  }

  /**
   * Data provider for testMain().
   */
  public static function dataProviderMain() {
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
        'ERROR: File "somefile" does not exist.',
      ],
    ];
  }

  /**
   * Test checking.
   *
   * @covers ::main
   * @covers ::print_help
   * @covers ::verbose
   * @dataProvider dataProviderMainFunc
   */
  public function testMainFunc(mixed $args = [], $expected_code = 0, $expected_output = [], $expected_exception_message = NULL, $should_fix = FALSE) {
    $file_before = NULL;
    $file_after = NULL;
    if (is_array($args) && count($args) > 0) {
      $fixture_files = $this->createTmpFilesFromFixtures([$args[0], $args[0]]);
      $file_before = array_shift($fixture_files);
      $file_after = array_shift($fixture_files);
      $args[0] = $file_before;
    }

    if ($expected_exception_message) {
      $this->expectException(\Exception::class);
      $this->expectExceptionMessage($expected_exception_message);
    }

    $result = $this->runMain($args);

    $this->assertEquals($expected_code, $result['code']);
    foreach ($expected_output as $expected_output_string) {
      $this->assertArrayContainsString($expected_output_string, $result['output']);
    }

    if ($file_before && $file_after) {
      if ($should_fix) {
        $this->assertFileNotEquals($file_after, $file_before);
      }
      else {
        $this->assertFileEquals($file_after, $file_before);
      }
    }
  }

  /**
   * Data provider for testMainFunc().
   */
  public static function dataProviderMainFunc() {
    return [
      ['-?', static::EXIT_SUCCESS, 'Check if shell script variables are wrapped in ${} and fix violations.'],
      [NULL, static::EXIT_ERROR, NULL, 'Please provide a file to check.'],
      [NULL, static::EXIT_ERROR, NULL, 'Please provide a file to check.'],
      ['non-existing', static::EXIT_ERROR, NULL, 'File "non-existing" does not exist.'],
      [[static::$fixtureFiles['valid']], static::EXIT_SUCCESS],
      [[static::$fixtureFiles['valid'], '--fix'], static::EXIT_SUCCESS],
      [[static::$fixtureFiles['invalid'], '--fix'], static::EXIT_SUCCESS, ['Replaced 3 variables in file'], NULL, TRUE],
      [[static::$fixtureFiles['invalid']], static::EXIT_ERROR, ['10: var=$VAR1', '11: var="$VAR2"', '13: var=$VAR3'], NULL, FALSE],
    ];
  }

  /**
   * @covers       ::process_line
   * @dataProvider dataProviderProcessLine
   */
  public function testProcessLine($actual, $expected) {
    $this->assertEquals($expected, process_line($actual));
  }

  /**
   * Data provider for testProcessLine().
   */
  public static function dataProviderProcessLine(): array {
    return [
      ['', ''],

      ['#', '#'],
      ['# word', '# word'],
      ['# $var', '# $var'],
      ['# word $var word', '# word $var word'],
      ['# $VAR', '# $VAR'],
      ['# word $VAR word', '# word $VAR word'],
      ['# \$VAR', '# \$VAR'],
      ['# word \$VAR word', '# word \$VAR word'],

      ['$var', '${var}'],
      ['$VAR', '${VAR}'],
      ['word $var word', 'word ${var} word'],
      ['word $VAR word', 'word ${VAR} word'],

      ['\$var', '\$var'],
      ['\$VAR', '\$VAR'],
      ['word \$var word', 'word \$var word'],
      ['word \$VAR word', 'word \$VAR word'],

      ['${var}', '${var}'],
      ['${var:-}', '${var:-}'],

      ['${var:-$other}', '${var:-${other}}'],
      ['${var:-${other}}', '${var:-${other}}'],
      ['${var:-${other:-}}', '${var:-${other:-}}'],

      ['"$var"', '"${var}"'],
      ['"\$var"', '"\$var"'],
      ['\'$var\'', '\'$var\''],
      ['\'\$var\'', '\'\$var\''],

      ['${var:-"$other"}', '${var:-"${other}"}'],
    ];
  }

}
