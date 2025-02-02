--TEST--
FR #78270 (Usage of __vectorcall convention with FFI)
--SKIPIF--
<?php
require_once('skipif.inc');
$dll = 'php8' . (PHP_ZTS ? 'ts' : '') . (PHP_DEBUG ? '_debug' : '') . '.dll';
try {
    FFI::cdef(<<<EOC
        __vectorcall int zend_atoi(const char *str, size_t str_len);
        EOC, $dll);
} catch (FFI\ParserException $ex) {
    die('skip __vectorcall not supported');
}
?>
--FILE--
<?php
$x86 = (PHP_INT_SIZE === 4);
$arglists = array(
    'int, int, int, int, int, int, int' => true,
    'double, int, int, int, int, int, int' => !$x86,
    'int, double, int, int, int, int, int' => !$x86,
    'int, int, double, int, int, int, int' => !$x86,
    'int, int, int, double, int, int, int' => !$x86,
    'int, int, int, int, double, int, int' => false,
    'int, int, int, int, int, double, int' => false,
    'int, int, int, int, int, int, double' => true,
);
foreach ($arglists as $arglist => $allowed) {
    $signature = "__vectorcall void foobar($arglist);";
    try {
        $ffi = FFI::cdef($signature);
    } catch (FFI\ParserException $ex) {
        if ($allowed) {
            echo "($arglist): unexpected ParserException\n";
        }
    } catch (FFI\Exception $ex) {
        if (!$allowed) {
            echo "($arglist): unexpected Exception\n";
        }
    }
}
?>
--EXPECT--
