<?php
/**
 * Created by IntelliJ IDEA.
 * User: Scuola
 * Date: 10/09/2015
 * Time: 17:45
 * @param $inizio
 * @return array
 */

function getNick($s) {
    return substr($s,4,strlen($s));
}
function isSit($s) {
    return startWith($s,"sit_");
}