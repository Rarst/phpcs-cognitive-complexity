<?php

/**
 * https://github.com/Rarst/phpcs-cognitive-complexity/issues/3
 */
function switchCaseTest()
{
    $something = false;
    $things = array();
    $blah = array();
    for ($i = 0, $count = \count($things); $i < $count; $i++) {
        switch ($things[$i]['method']) {
            case 'foo':
            case 'bar':
                $blah[] = $i;
                break;
            case 'baz':
                \array_pop($blah);
                break;
            case 'ding':
            case 'dong':
                if ($something === false) {
                    break;
                }
                foreach ($blah as $i2) {
                    $things[$i2]['method'] = 'foo';
                }
                break;
        }
    }
}

// Cognitive Complexity 9
