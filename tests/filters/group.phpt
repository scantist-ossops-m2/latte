<?php

/**
 * Test: Latte\Essential\Filters::group()
 */

declare(strict_types=1);

use Latte\Essential\Filters;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function iterator(): Generator
{
	yield ['a' => 55] => ['k' => 22, 'k2'];
	yield ['a' => 66] => (object) ['k' => 22, 'k2'];
	yield ['a' => 77] => ['k' => 11];
	yield ['a' => 88] => ['k' => 33];
}


function exportIterator(Traversable $iterator): array
{
	$res = [];
	foreach ($iterator as $key => $value) {
		$res[] = [$key, $value instanceof Traversable ? exportIterator($value) : $value];
	}
	return $res;
}


test('array', function () {
	Assert::equal(
		[
			[22, [
				[0, ['k' => 22, 'k2']],
				[1, (object) ['k' => 22, 'k2']],
			]],
			[11, [[2, ['k' => 11]]]],
			[33, [[3, ['k' => 33]]]],
		],
		exportIterator(Filters::group(
			[['k' => 22, 'k2'], (object) ['k' => 22, 'k2'], ['k' => 11], ['k' => 33]],
			'k',
		)),
	);
	Assert::same([], exportIterator(Filters::group([], 'k')));
});


test('iterator', function () {
	Assert::equal(
		[
			[22, [
				[['a' => 55], ['k' => 22, 'k2']],
				[['a' => 66], (object) ['k' => 22, 'k2']],
			]],
			[11, [[['a' => 77], ['k' => 11]]]],
			[33, [[['a' => 88], ['k' => 33]]]],
		],
		exportIterator(Filters::group(iterator(), 'k')),
	);
});


test('array + callback', function () {
	Assert::same(
		[[220, [[0, 22]]], [110, [[1, 11]]], [330, [[2, 33]]]],
		exportIterator(Filters::group([22, 11, 33], fn($a) => $a * 10)),
	);
});


test('iterator + callback', function () {
	Assert::equal(
		[
			[-22, [
				[['a' => 55], ['k' => 22, 'k2']],
				[['a' => 66], (object) ['k' => 22, 'k2']],
			]],
			[-11, [[['a' => 77], ['k' => 11]]]],
			[-33, [[['a' => 88], ['k' => 33]]]],
		],
		exportIterator(Filters::group(iterator(), fn($a) => -((array) $a)['k'])),
	);
});
