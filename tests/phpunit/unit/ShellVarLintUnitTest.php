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
   * @group main
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
   * @group unit
   */
  public function testProcessLine($actual, $expected) {
    $this->assertEquals($expected, process_line($actual));
  }

  /**
   * Data provider for testProcessLine().
   *
   * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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

      // Contains underscore.
      ['$var_longer_123', '${var_longer_123}'],
      ['$VAR_LONGER_123', '${VAR_LONGER_123}'],
      ['word $var_longer_123 word', 'word ${var_longer_123} word'],
      ['word $VAR_LONGER_123 word', 'word ${VAR_LONGER_123} word'],

      ['\$var_longer_123', '\$var_longer_123'],
      ['\$VAR_LONGER_123', '\$VAR_LONGER_123'],
      ['word \$var_longer_123 word', 'word \$var_longer_123 word'],
      ['word \$VAR_LONGER_123 word', 'word \$VAR_LONGER_123 word'],

      ['${var_longer_123}', '${var_longer_123}'],
      ['${var_longer_123:-}', '${var_longer_123:-}'],

      ['${var_longer_123:-$other}', '${var_longer_123:-${other}}'],
      ['${var_longer_123:-${other}}', '${var_longer_123:-${other}}'],
      ['${var_longer_123:-${other:-}}', '${var_longer_123:-${other:-}}'],

      ['"$var_longer_123"', '"${var_longer_123}"'],
      ['"\$var_longer_123"', '"\$var_longer_123"'],
      ['\'$var_longer_123\'', '\'$var_longer_123\''],
      ['\'\$var_longer_123\'', '\'\$var_longer_123\''],

      ['${var_longer_123:-"$other"}', '${var_longer_123:-"${other}"}'],

      // Starts with underscore.
      ['$_var_longer_123', '${_var_longer_123}'],
      ['$_VAR_LONGER_123', '${_VAR_LONGER_123}'],
      ['word $_var_longer_123 word', 'word ${_var_longer_123} word'],
      ['word $_VAR_LONGER_123 word', 'word ${_VAR_LONGER_123} word'],

      ['\$_var_longer_123', '\$_var_longer_123'],
      ['\$_VAR_LONGER_123', '\$_VAR_LONGER_123'],
      ['word \$_var_longer_123 word', 'word \$_var_longer_123 word'],
      ['word \$_VAR_LONGER_123 word', 'word \$_VAR_LONGER_123 word'],

      ['${_var_longer_123}', '${_var_longer_123}'],
      ['${_var_longer_123:-}', '${_var_longer_123:-}'],

      ['${_var_longer_123:-$other}', '${_var_longer_123:-${other}}'],
      ['${_var_longer_123:-${other}}', '${_var_longer_123:-${other}}'],
      ['${_var_longer_123:-${other:-}}', '${_var_longer_123:-${other:-}}'],

      ['"$_var_longer_123"', '"${_var_longer_123}"'],
      ['"\$_var_longer_123"', '"\$_var_longer_123"'],
      ['\'$_var_longer_123\'', '\'$_var_longer_123\''],
      ['\'\$_var_longer_123\'', '\'\$_var_longer_123\''],
      ['${_var_longer_123:-"$other"}', '${_var_longer_123:-"${other}"}'],

      // Quotes within quotes.
      ['"\'$var\'"', '"\'${var}\'"'],
      ['"word \'$var\' word"', '"word \'${var}\' word"'],
      // And with escaped.
      ['"\'\$var\'"', '"\'\$var\'"'],

      ['string with $var1 "\'$var2\'" \'$var3\'', 'string with ${var1} "\'${var2}\'" \'$var3\''],
      ['string with $var1 "\'\$var2\'" \'$var3\'', 'string with ${var1} "\'\$var2\'" \'$var3\''],

      // Arrays.
      ['${_var_longer_array[$_var_longer_key]}', '${_var_longer_array[${_var_longer_key}]}'],
      ['${_var_longer_array["$_var_longer_key"]}', '${_var_longer_array["${_var_longer_key}"]}'],
      ['"${_var_longer_array["$_var_longer_key"]}"', '"${_var_longer_array["${_var_longer_key}"]}"'],

      ['echo "  \\$config[\'stage_file_proxy.settings\'][\'origin\'] = \'http://www.resistance-star-wars.com/\';"', 'echo "  \\$config[\'stage_file_proxy.settings\'][\'origin\'] = \'http://www.resistance-star-wars.com/\';"'],
    ];
  }

  /**
   * @covers      ::is_interpolation
   * @dataProvider dataProviderIsInterpolation
   * @group unit
   */
  public function testIsInterpolation($line, $expected) {
    $pos = strpos($line, 'var');
    $pos = $pos === FALSE ? 0 : $pos;
    $this->assertEquals($expected, is_interpolation($line, $pos));
  }

  /**
   * Data provider for testIsInterpolation().
   */
  public static function dataProviderIsInterpolation() {
    return [
      ['', FALSE],
      ['var', TRUE],
      [' var ', TRUE],
      ['"var"', TRUE],
      [' "var" ', TRUE],
      [' " var " ', TRUE],
      ['\'var\'', FALSE],
      [' \'var\' ', FALSE],
      [' \' var \' ', FALSE],
      ['"\'var\'"', TRUE],
      [' "\'var\'"', TRUE],
      [' "\' var\'"', TRUE],
      [' "\' var\'" ', TRUE],
      [' "\' var\' " ', TRUE],
      [' "\' var \' " ', TRUE],

      ['\'"var"\'', TRUE],
      [' \'"var"\'', TRUE],
      [' \' "var"\'', TRUE],
      [' \' " var"\'', TRUE],
      [' \' " var" \'', TRUE],
      [' \' " var" \' ', TRUE],
      [' \' " other var" \' ', TRUE],

      ['"other" \'"var"\'', TRUE],
      [' "other" \'" var"\'', TRUE],
      [' "other" \'" var "\'', TRUE],
      [' "other " \'" var "\'', TRUE],
      [' "other " \'  " var "\'', TRUE],
      ['"other \'in single\'" \'"var"\'', TRUE],
      ['"other \'  in single\' " \'"var"\'', TRUE],

      ['"other"\'\'var\'', FALSE],
      [' "other"\'\'var\'', FALSE],
      [' "other" \'\'var\'', FALSE],
      [' "other" \' \'var\'', FALSE],
      [' "other" \' \' var\'', FALSE],
      [' "other"\' \' var\'', FALSE],
      ['"other"\'\'\'var\'', FALSE],
      [' "other"\' \'\'var\'', FALSE],
      ['"other \'quoted\' \'var\' "', TRUE],
      ['"other \' quoted\' \'var\' "', TRUE],
      [' "other \' quoted\' \'var\' "', TRUE],
      [' " other \' quoted\' \'var\' "', TRUE],

      // Broken, but starts with double.
      ['\'single"other \'in single\'" \'"var"\'', TRUE],
      // Broken, but unmatched double.
      ['\'single"other \'in single\'" "\'var\'', TRUE],
      // Broken - unmatched single.
      ['\'single"other \'in single\'" \'var\'', FALSE],
    ];
  }

}
