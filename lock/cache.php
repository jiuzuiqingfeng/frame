<?php

function singly_run($key, $expire_second, closure $closure, $fail_closure = null)
{/*{{{*/
    $lock_key = 'singly_run_'.$key;

    $lock_num = cache_increment($lock_key, 1, $expire_second);

    $locked = ($lock_num > 1);

    if (! $locked) {
        $res = call_user_func($closure);

        cache_delete($lock_key);
    } else {
        $res = value($fail_closure);
    }

    return $res;
}/*}}}*/

function serially_run($key, $expire_second, $wait_second, closure $closure, $fail_closure = null)
{/*{{{*/
    $sleep_wait = 500000;
    $total_wait_time = 0;
    $wait_usecond = $wait_second * 1000000;

    $lock_key = 'serially_run_'.$key;

    while (
        $total_wait_time < $wait_usecond
        && ($t = cache_increment($lock_key, 1, $expire_second)) > 1
    ) {
        $total_wait_time += $sleep_wait;
        usleep($sleep_wait);
    }

    if ($total_wait_time < $wait_usecond) {
        $res = call_user_func($closure);

        cache_delete($lock_key);
    } else {
        $res = value($fail_closure);
    }

    return $res;
}/*}}}*/
