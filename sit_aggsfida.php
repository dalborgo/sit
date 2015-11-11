<?php
/**
 * Created by IntelliJ IDEA.
 * User: Scuola
 * Date: 13/05/2015
 * Time: 15:29
 */

include "librerie/sql.php";
include "librerie/fetch.php";
include "librerie/stringhe.php";
include "librerie/specific.php";
include "librerie/date.php";
require_once "librerie/simple_html_dom.php";

$sq = query("SELECT * from ss_sfida");
while (($ra = mysql_fetch_assoc($sq))) {

    $id=$ra["id"];
    $d1=dats(chiamate($ra["nick1"]));
    $d2=dats(chiamate($ra["nick2"]));
    if($d1[0]===$d2[0]){
        echo "matchano<br>".$d1[0]."<br>".$d2[0];
        query("UPDATE ss_sfida SET descr='$d1[1]', totale='$d1[2]' WHERE id='$id'");
    }else{
        echo "errore<br>".$d1[0]."<br>".$d2[0];
    }
}
function chiamate($lui){
    $service_url = 'http://www.sharkscope.com/api/dalborgo/playergroups/sit_'.$lui;
    $decoded = ccall($service_url);
    return $decoded->Response->PlayerGroupResponse->PlayerGroup->Players->Player;
}
function dats($ltot) {
    $res="";
    $val="";
    if(!is_array($ltot)){
        $mio=$ltot;
        $r=creaStr($mio);
        $res.=$r[0];
        $val[$r[1]]=$r[2];
    }else
        foreach ($ltot as $mio) {
            $r=creaStr($mio);
            $res.=$r[0];
            $val[$r[1]]=$r[2];
        }
    $yu[0]=$res;
    $gg=creaDescr($val);
    $yu[1]=$gg[0];
    $yu[2]=$gg[1];
    return $yu;
}
function creaDescr($d){
    $re="";
    $tot=0;
    foreach ($d as $k => $v ) {
        $re.=$k.": Inizio: ".$v["inizio"]." - Tipo: &euro;".$v["StakePlusRake"]." (".$v["Entrants"]." man) - Target: ".$v["totale"]." sit<br>";
        $tot+=$v["totale"];
    }
    $f=array();
    $f[]=$re;
    $f[]=$tot;
    return $f;
}
function creaStr($mio){
    $out= array();
    $out["nick"] = $mio->{'@name'};
    $out["network"] = $mio->{'@network'};
    $para=$mio->Filter;
    $str=$para->{'@parameters'};
    //$a=explode(";",$str);
    $valo=array();
    foreach ($para->Constraint as $l) {
        if($l->{'@id'}=="Date") {
            $e=explode('~',$l->{'Value'});
            $valo["inizio"] = "Inizio: ".date('d/m/Y',$e[0]);
        }
        if($l->{'@id'}=="Limit") {
            $valo["totale"] = $l->{'Maximum'};
            echo "";
        }
        if($l->{'@id'}=="Entrants") {
            $valo["Entrants"] = $l->{'Maximum'};
            echo "";
        }
        if($l->{'@id'}=="StakePlusRake") {
            $valo["StakePlusRake"] = $l->StakeEntry->{'@amount'};
            echo "";
        }
    }
    $out[]=$str;
    $out[]=$out["network"];
    $out[]=$valo;
    return $out;
}
