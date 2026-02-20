<?php

return [
    'summer_on_december' => env('MALULU_SUMMER_ON_DECEMBER', true),
    'one_school_cue' => env('MALULU_ONE_SCHOOL_CUE', ''),
    'one_school_only_primary' => env('MALULU_ONE_SCHOOL_ONLY_PRIMARY', false),
    'simple_test_scenario' => filter_var(env('MALULU_SIMPLE_TEST_SCENARIO', false), FILTER_VALIDATE_BOOLEAN),
];
