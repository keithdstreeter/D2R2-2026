<?php

$dataPath = dirname(__DIR__, 3).'/database/data';

it('loads registrations seed data using the exact device-safe filename casing', function () {
    $migration = require dirname(__DIR__, 3).'/database/migrations/2026_07_02_214600_seed_registrations_data.php';
    $method = new ReflectionMethod($migration, 'loadJson');
    $method->setAccessible(true);
    $data = $method->invoke($migration, 'Registrations.json');

    expect($data)->toBeArray()->not->toBeEmpty()
        ->and($data[0])->toHaveKeys(['bib', 'first_name', 'last_name', 'dob']);
});

it('loads cuesheet seed data regardless of filename casing', function () {
    $migration = require dirname(__DIR__, 3).'/database/migrations/2026_07_28_213400_seed_cuesheets_data.php';
    $method = new ReflectionMethod($migration, 'loadJson');
    $method->setAccessible(true);
    $data = $method->invoke($migration, 'cuesheet_100k.json');

    expect($data)->toBeArray()->not->toBeEmpty()
        ->and($data[0])->toHaveKeys(['ride', 'turn', 'notes', 'distance']);
});

it('has valid JSON in each seed data file', function (string $file) {
    $content = file_get_contents($file);
    $decoded = json_decode($content, true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE)
        ->and($decoded)->toBeArray()->not->toBeEmpty();
})->with(function () use ($dataPath) {
    return collect(glob($dataPath.'/*.json'))
        ->mapWithKeys(fn (string $file) => [basename($file) => $file])
        ->all();
});

it('has exactly 4 choices with labels A-D for each question', function (string $file) {
    $questions = json_decode(file_get_contents($file), true);

    foreach ($questions as $index => $question) {
        $labels = collect($question['choices'])->pluck('label')->sort()->values()->all();

        expect($question['choices'])->toHaveCount(4, "Question #{$index} does not have 4 choices")
            ->and($labels)->toBe(['A', 'B', 'C', 'D'], "Question #{$index} labels are not A-D");
    }
})->with(function () use ($dataPath) {
    return collect(glob($dataPath.'/questions_*.json'))
        ->mapWithKeys(fn (string $file) => [basename($file) => $file])
        ->all();
});

it('has exactly one correct answer per question', function (string $file) {
    $questions = json_decode(file_get_contents($file), true);

    foreach ($questions as $index => $question) {
        $correctCount = collect($question['choices'])->where('is_correct', true)->count();

        expect($correctCount)->toBe(1, "Question #{$index} has {$correctCount} correct answers instead of 1");
    }
})->with(function () use ($dataPath) {
    return collect(glob($dataPath.'/questions_*.json'))
        ->mapWithKeys(fn (string $file) => [basename($file) => $file])
        ->all();
});
