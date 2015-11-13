<?php
/**
 * Created by IntelliJ IDEA.
 * User: Scuola
 * Date: 10/06/2015
 * Time: 05:17
 */
include "librerie/sql.php";
include "librerie/fetch.php";
if(isset($_GET['id']))
    $ids=$_GET['id'];
else
    $ids="1";
//$ore="24H";
$sq = query("SELECT * from ss_sfida where id='$ids'");
while (($ra = mysql_fetch_assoc($sq))) {
    $id=$ra["id"];
    $value=$ra["nick1"];
    $value2=$ra["nick2"];
    $totale=$ra["totale"];
}
$tipo="Last,$totale";
$res=ccall('http://www.sharkscope.com/api/dalborgo/networks/PlayerGroup/players/sit_'.$value.'/completedTournaments?order='.$tipo);
$res2=ccall('http://www.sharkscope.com/api/dalborgo/networks/PlayerGroup/players/sit_'.$value2.'/completedTournaments?order='.$tipo);
//echo"";
$base1=dati($res);
$base2=dati($res2);
$fd=$base1->str;
$fd2=$base2->str;
$diff=($base1->ult>=$base2->ult)?$base1->ult-$base2->ult:$base2->ult-$base1->ult;
$wow=$base1->arr;
$wow2=$base2->arr;
$titolo="<div style='text-align:center'>";
if($base1->ult>$base2->ult)
    $titolo.="<span style='color:blue'><b>".$value."</span> + &euro;".$diff."</b>";
else if($base2->ult>$base1->ult)
    $titolo.="<span style='color:red'><b>".$value2."</span> + &euro;".$diff."</b>";
else
    $titolo.=$value." ALL SQUARE ".$value2 ;
$titolo.="<br><span style='font-size:medium'>Target: $totale sit </span></div>";
function dati($res,$sec=0)
{
    $vuoto= new stdClass();
    $vuoto->ult=0;
    $vuoto->str="";
    $vuoto->arr='{}';
    $somma=0;
    $str = "";
    $ult = 0;
    $gioc2 = $res->Response->PlayerResponse->PlayerView;
    if (!isset($gioc2->PlayerGroup->CompletedTournaments->Tournament))
        return $vuoto;
    $conto = count($gioc2->PlayerGroup->CompletedTournaments->Tournament);
    $out = array();
    $j=0;
    for( $i= $conto-1 ; $i >= 0 ; $i-- ){
        //foreach ($gioc2->PlayerGroup->CompletedTournaments->Tournament as $key => $value) {
        if ($conto < 2)
            $value = $gioc2->PlayerGroup->CompletedTournaments->Tournament;
        else
            $value=$gioc2->PlayerGroup->CompletedTournaments->Tournament[$i];
        $datec = "";
        try {
            $datec = new DateTime("@" . $value->{'@date'});
            $datec->setTimezone(new DateTimeZone('Europe/Rome'));
            $datec = $datec->format('d/m/Y H:i');
        } catch (Exception $e) {
        }
        $pirce=0;$rake=0;$stake=0;$rebuy=0;$name="";$net="";$pos="";
        if (isset($value->TournamentEntry->{'@prize'}))
            $pirce=$value->TournamentEntry->{'@prize'};
        if (isset($value->TournamentEntry->{'@position'}))
            $pos=$value->TournamentEntry->{'@position'};
        if (isset($value->{'@rake'}))
            $rake=$value->{'@rake'};
        if (isset($value->{'@name'}))
            $name=$value->{'@name'};
        if (isset($value->{'@stake'}))
            $stake=$value->{'@stake'};
        if (isset($value->{'@network'}))
            $net=$value->{'@network'};
        if (isset($value->{'@rebuyStake'}))
            $rebuy=$value->{'@rebuyStake'};
        $guad=0;
        if($rebuy)
            $guad=$pirce-($rake+$rebuy);
        else
            $guad=$pirce-($stake+$rake);
        $somma+=$guad;
        $obj2 = new stdClass();
        $obj2->data=$datec;
        $obj2->guad=($guad>=0)?'<span style="color:green">&euro; '.$guad.'</span>':'<span style="color:red">&euro; '.$guad.'</span>';
        $obj2->nome=$name;
        $obj2->net=$net;
        $obj2->pos=$pos.'&deg;';
        $out[++$j]=$obj2;
        //$somma=number_format($somma,2);
        if($j==$conto){
            $fre="{
                dataLabels: {
                enabled: true,
                    align: 'left',
                    format: '\u20AC {y:.2f}',
                    style: {
                    fontWeight: 'bold'
                    },
                    x: 3,
                    verticalAlign: 'middle',
                    overflow: true,
                    crop: false
                },
                y: $somma
            }";
            $str.=$fre;
            $ult=$somma;
        }else
            $str.=$somma.",";
        if ($conto < 2)
            return;
    }
    $obj = new stdClass();
    $obj->str=$str;
    $obj->arr=json_encode($out);
    $obj->ult=$ult;
    return $obj;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $value ?> VS <?php echo $value2 ?></title>
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <!-- Additional files for the Highslide popup effect -->
    <script src="https://www.highcharts.com/samples/static/highslide-full.min.js"></script>
    <script src="https://www.highcharts.com/samples/static/highslide.config.js" charset="utf-8"></script>
    <link rel="stylesheet" type="text/css" href="https://www.highcharts.com/samples/static/highslide.css" />

    <meta charset="UTF-8">
    <script type="application/javascript">
        var wow = <?php echo $wow ?>;
        var wow2 = <?php echo $wow2 ?>;
        $(function () {
            $('#container').highcharts({
                title: {
                    text: <?php echo '"'.$titolo.'"' ?>,
                    useHTML: true
                },
                chart: {
                    marginRight: 90,
                    backgroundColor: "#FFFBF0",
                    borderRadius: 8,
                    borderWidth: 1


                },
                yAxis: {
                    title: {
                        text: 'Guadagno'
                    },
                    labels: {
                        formatter: function() {
                            return '\u20AC '+ this.value ;
                        }
                    },plotBands: [{
                        from: 0,
                        to: 10000,
                        color: 'rgba(68, 170, 213, 0.1)',
                        zIndex: 3,
                        label: {
                            text: 'Giorni felici',
                            style: {
                                color: '#606060'
                            }
                        }
                    },{
                        from: 0,
                        to: -10000,
                        color: 'rgba(159, 144, 144, 0.1)',
                        zIndex: 3,
                        label: {
                            text: 'Giorni grigi',
                            style: {
                                color: '#606060'
                            }
                        }
                    }]
                },
                xAxis: {
                    title: {
                        text: 'Tornei'
                    },
                    allowDecimals: false
                },
                tooltip: {
                    crosshairs: true,
                    formatter: function() {
                        var sm = [];
                        var si = '';
                        var maxi ='-10000';
                        var counter = [];
                        var i2=0;
                        $.each(this.points, function(i, point) {
                            sm[i]='<span style=\"color:' + point.series.color + ';font-weight:bold\">' + point.series.name + ': \u20AC ' + point.y.toFixed(2) + '</span>';
                            counter[i++] = point.y;

                        });
                        var diff=0;
                        var tornei='<b>Tornei: '+this.x+'</b><br/>';
                        if(counter[0] > counter[1]) {
                            diff = counter[0] - counter[1];
                            si =tornei+'<b>1\xB0 </b> '+sm[0] + '<br/><b>2\xB0 </b> ' + sm[1] + '<br/><b>diff: ' + diff.toFixed(2) + '</b>';
                        }
                        else {
                            diff = counter[1] - counter[0];
                            sm[1]=(sm[1])?sm[1]+'<br/>':"";
                            diff=(diff)?''+diff.toFixed(2):"";
                            if(diff=="")
                                si=tornei+''+sm[1]+''+sm[0];
                            else
                                si=tornei+'<b>1\xB0 </b> '+sm[1]+'<b>2\xB0 </b>'+sm[0]+'<br/><b>Diff: ' + diff + '</b>';
                        }
                        return si;
                    },
                    shared: true
                },
                plotOptions: {
                    series: {
                        cursor: 'pointer',
                        point: {
                            events: {
                                click: function (e) {
                                    if(this.series.index == 0)
                                        hs.htmlExpand(null, {
                                            pageOrigin: {
                                                x: e.pageX || e.clientX,
                                                y: e.pageY || e.clientY
                                            },
                                            headingText: this.series.name+': '+wow[this.x].guad,
                                            maincontentText: wow[this.x].data+'<br/><b>'+wow[this.x].nome+' - ['+wow[this.x].pos+']</b><br/><i>'+wow[this.x].net+'</i>',
                                            width: 200
                                        });
                                    else
                                        hs.htmlExpand(null, {
                                            pageOrigin: {
                                                x: e.pageX || e.clientX,
                                                y: e.pageY || e.clientY
                                            },
                                            headingText: this.series.name+': '+wow2[this.x].guad,
                                            maincontentText: wow2[this.x].data+'<br/><b>'+wow2[this.x].nome+' - ['+wow2[this.x].pos+']</b><br/><i>'+wow2[this.x].net+'</i>',
                                            width: 200
                                        });
                                }
                            }
                        },
                        marker: {
                            lineWidth: 1
                        }
                    }
                },
                series: [{name:<?php echo "'$value'" ?>,
                    data: [0,<?php echo $fd ?>],color:'#0000FF',lineWidth: 2
                }, {name:<?php echo "'$value2'" ?>,
                    data: [0,<?php echo $fd2 ?>],color: '#FF0000',lineWidth: 2
                }]
            });
        });
    </script>
    <style>
        .draggable-header .highslide-maincontent {
            padding-top: 0.5em;
            line-height: 16px;
        }
        .highslide-container div {
            font-family: Verdana,Helvetica;
            font-size: 8pt;
        }
        .draggable-header .highslide-heading {
            font-size: 11pt;
            margin: 2px 0.4em;
            position: absolute;
        }
        li.highslide-close {
            background-image: url('media/images/close.png');
            background-repeat: no-repeat;
        }
    </style>
</head>

<body>
<div id="container" style="height: 500px;width:100%;"></div>
</body>

</html>
