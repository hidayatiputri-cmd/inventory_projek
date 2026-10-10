<?php
// helpers/validation.php

function validate_required($value) {
    return !empty(trim($value));
}

function validate_positive_number($value) {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false && $value >= 0;
}