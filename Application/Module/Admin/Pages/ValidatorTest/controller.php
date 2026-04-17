<?php

$tests = [];

$run = function (string $group, string $label, mixed $actual, mixed $expected) use (&$tests): void {
    $tests[] = [
        'group'    => $group,
        'label'    => $label,
        'pass'     => $actual === $expected,
        'actual'   => var_export($actual, true),
        'expected' => var_export($expected, true),
    ];
};

// ── required() ───────────────────────────────────────────────────────────────
$run('required', 'Empty string → error',       Validator::required('',      'Name'), 'Name is required.');
$run('required', 'Whitespace only → error',    Validator::required('   ',   'Name'), 'Name is required.');
$run('required', 'Non-empty string → null',    Validator::required('Alice', 'Name'), null);
$run('required', 'Number as string → null',    Validator::required('0',     'Name'), null);

// ── email() ───────────────────────────────────────────────────────────────────
$run('email', 'No @ symbol → error',           Validator::email('notanemail',    'email') !== null, true);
$run('email', 'Missing TLD → error',           Validator::email('a@b',           'email') !== null, true);
$run('email', 'Valid address → null',          Validator::email('a@b.com',       'email'), null);
$run('email', 'Valid with subdomain → null',   Validator::email('x@mail.co.uk',  'email'), null);

// ── minLength() ───────────────────────────────────────────────────────────────
$run('minLength', '5 chars, min 8 → error',    Validator::minLength('short',      8, 'Password') !== null, true);
$run('minLength', '7 chars, min 8 → error',    Validator::minLength('1234567',    8, 'Password') !== null, true);
$run('minLength', 'Exactly 8 chars → null',    Validator::minLength('12345678',   8, 'Password'), null);
$run('minLength', '10 chars, min 8 → null',    Validator::minLength('longenough', 8, 'Password'), null);
$run('minLength', 'Padded to 8 by spaces → error', Validator::minLength('abc    ', 8, 'Password') !== null, true);

// ── enum() ────────────────────────────────────────────────────────────────────
$run('enum', 'Valid value → kept',             Validator::enum('Active', ['Active', 'Inactive'], 'Active'), 'Active');
$run('enum', 'Second valid value → kept',      Validator::enum('Inactive', ['Active', 'Inactive'], 'Active'), 'Inactive');
$run('enum', 'Invalid value → default',        Validator::enum('Bad',    ['Active', 'Inactive'], 'Active'), 'Active');
$run('enum', 'Empty string → default',         Validator::enum('',       ['Active', 'Inactive'], 'Active'), 'Active');
$run('enum', 'Wrong case → default',           Validator::enum('active', ['Active', 'Inactive'], 'Active'), 'Active');

// ── boolean() ─────────────────────────────────────────────────────────────────
$run('boolean', 'Empty string → 0',            Validator::boolean(''),    0);
$run('boolean', 'null → 0',                    Validator::boolean(null),  0);
$run('boolean', 'false → 0',                   Validator::boolean(false), 0);
$run('boolean', '"1" → 1',                     Validator::boolean('1'),   1);
$run('boolean', 'true → 1',                    Validator::boolean(true),  1);
$run('boolean', '"on" → 1',                    Validator::boolean('on'),  1);

// ── nullableInt() ─────────────────────────────────────────────────────────────
$run('nullableInt', 'Empty string → null',     Validator::nullableInt(''),    null);
$run('nullableInt', 'null → null',             Validator::nullableInt(null),  null);
$run('nullableInt', '"5" → 5',                 Validator::nullableInt('5'),   5);
$run('nullableInt', '42 (int) → 42',           Validator::nullableInt(42),    42);
$run('nullableInt', '"3.9" → 3 (truncates)',   Validator::nullableInt('3.9'), 3);

// ── nullableFloat() ───────────────────────────────────────────────────────────
$run('nullableFloat', 'Empty string → null',   Validator::nullableFloat(''),      null);
$run('nullableFloat', 'null → null',           Validator::nullableFloat(null),    null);
$run('nullableFloat', '"3.14" → 3.14',         Validator::nullableFloat('3.14'),  3.14);
$run('nullableFloat', '"0.5" → 0.5',           Validator::nullableFloat('0.5'),   0.5);
$run('nullableFloat', '7 (int) → 7.0',         Validator::nullableFloat(7),       7.0);

// ── Summary ───────────────────────────────────────────────────────────────────
$passed = count(array_filter($tests, fn($t) => $t['pass']));
$total  = count($tests);
$failed = $total - $passed;

$groups = [];
foreach ($tests as $t) {
    $groups[$t['group']][] = $t;
}
