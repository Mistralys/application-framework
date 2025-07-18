<?php

declare(strict_types=1);

echo sb()->codeCopy('Text to copy');

echo '<hr>';

// Empty text
echo sb()->codeCopy('');

echo '<hr>';

// Custom empty text label
echo sb()->codeCopy('', 'No text entered');
