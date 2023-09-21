<?php

namespace AlexSkrypnyk\ShellVarLint\Tests\Unit;

use AlexSkrypnyk\ShellVarLint\Tests\Traits\ArrayTrait;
use AlexSkrypnyk\ShellVarLint\Tests\Traits\AssertTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ScriptUnitTestCase.
 *
 * Base class to unit test scripts.
 */
abstract class ScriptUnitTestCase extends TestCase {

  use ArrayTrait;
  use AssertTrait;

  /**
   * Script to include.
   *
   * @var string
   */
  protected static $script = 'shell-var-lint';

  /**
   * Temporary directory.
   *
   * @var string
   */
  protected $tmpDir;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Prevent script from running.
    putenv('SCRIPT_RUN_SKIP=1');
    // Log output into internal buffer instead of stdout so we can assert it.
    putenv('SCRIPT_QUIET=1');

    if (!is_readable(static::$script)) {
      throw new \RuntimeException(\sprintf('Unable to include script file %s.', static::$script));
    }
    require_once static::$script;

    $this->tmpDir = $this->tempdir();

    parent::setUp();
  }

  /**
   * Run main() with optional arguments.
   *
   * @param string|array<string> $args
   *   Optional array of arguments to pass to the script.
   *
   * @return array<string>
   *   Array of output lines.
   */
  protected function runMain(string|array $args = []): array {
    $args = is_array($args) ? $args : [$args];

    $function = new \ReflectionFunction('main');
    array_unshift($args, $function->getFileName());
    $args = array_filter($args);

    main($args, count($args));

    return verbose('');
  }

  /**
   * Create temp files from fixtures.
   *
   * @param array $fixture_map
   *   Array of fixture mappings the following structure:
   *   - key: (string) Path to create.
   *   - value: (string) Path to a fixture file to use.
   * @param string $prefix
   *   Optional directory prefix.
   *
   * @return array
   *   Array of created files with the following structure:
   *   - key: (string) Source path (the key from $file_structure).
   *   - value: (string) Path to a fixture file to use.
   */
  protected function createTmpFilesFromFixtures(array $fixture_map, $prefix = NULL): array {
    $files = [];
    foreach ($fixture_map as $path => $fixture_file) {
      $tmp_path = $this->toTmpPath($path, $prefix);
      $dirname = dirname($tmp_path);

      if (!file_exists($dirname)) {
        mkdir($dirname, 0777, TRUE);
        if (!is_readable($dirname)) {
          throw new \RuntimeException(sprintf('Unable to create temp directory %s.', $dirname));
        }
      }

      // Pass-through preserving/removal values.
      if (is_bool($fixture_file)) {
        $files[$path] = $fixture_file;
        continue;
      }

      // Allow creating empty directories.
      if (empty($fixture_file) || $fixture_file === '.empty') {
        continue;
      }
      $fixture_file = $this->fixtureFile($fixture_file);

      copy($fixture_file, $tmp_path);
      $files[$path] = $tmp_path;
    }

    return $files;
  }

  /**
   * Path to a temporary file.
   */
  protected function toTmpPath(string $filename, string|null $prefix = NULL): string {
    return $prefix
      ? $this->tmpDir . DIRECTORY_SEPARATOR . $prefix . DIRECTORY_SEPARATOR . $filename
      : $this->tmpDir . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Replace path to a fixture file.
   */
  protected function fixtureFile(string $filename): string {
    $path = 'tests/phpunit/fixtures/' . $filename;
    if (!is_readable($path)) {
      throw new \RuntimeException(sprintf('Unable to find fixture file %s.', $path));
    }

    return $path;
  }

  /**
   * Create a random unique temporary directory.
   */
  protected function tempdir(string|null $dir = NULL, string $prefix = 'tmp_', int $mode = 0700, int $max_attempts = 1000): string {
    if (is_null($dir)) {
      $dir = sys_get_temp_dir();
    }

    $dir = rtrim($dir, DIRECTORY_SEPARATOR);

    if (!is_dir($dir) || !is_writable($dir)) {
      throw new \RuntimeException(sprintf('Unable to create temporary directory "%s".', $dir));
    }

    if (strpbrk($prefix, '\\/:*?"<>|') !== FALSE) {
      throw new \RuntimeException(sprintf('Unable to create temporary directory "%s".', $dir));
    }

    $attempts = 0;
    do {
      $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
    } while (!mkdir($path, $mode) && $attempts++ < $max_attempts);

    if (!is_dir($path) || !is_writable($path)) {
      throw new \RuntimeException(sprintf('Unable to create temporary directory "%s".', $path));
    }

    return $path;
  }

}
