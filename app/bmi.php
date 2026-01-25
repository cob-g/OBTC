<?php

function bmi_from_imperial($weightLbs, $heightFt, $heightIn)
{
    $weightLbs = (float) $weightLbs;
    $heightFt = (int) $heightFt;
    $heightIn = (int) $heightIn;

    $totalIn = ($heightFt * 12) + $heightIn;
    if ($totalIn <= 0) {
        return null;
    }

    return (703.0 * $weightLbs) / ($totalIn * $totalIn);
}

function bmi_category_suggest($bmi)
{
    if ($bmi === null) {
        return null;
    }

    $bmi = (float) $bmi;

    if ($bmi >= 35.0) {
        return 'Extremely Obese';
    }
    if ($bmi >= 30.0) {
        return 'Obese II';
    }
    if ($bmi >= 25.0) {
        return 'Obese I';
    }
    if ($bmi >= 23.0) {
        return 'Overweight';
    }
    if ($bmi >= 18.5) {
        return 'Normal';
    }

    return 'Underweight';
}

function bmi_category_options()
{
    return [
        'Normal',
        'Overweight',
        'Obese I',
        'Obese II',
        'Extremely Obese',
    ];
}
