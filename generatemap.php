<?
header('Content-Type: image/png');
require_once('db.inc.php');

session_start();

$systemlabel=0;
if (isset($_SESSION['systemlabel']))
{
    $systemlabel=$_SESSION['systemlabel'];
}
$regionlabel=0;
if (isset($_SESSION['regionlabel']))
{
    $regionlabel=$_SESSION['regionlabel'];
}


$x=800;
if (isset($_SESSION['mapx']))
{
    $x=$_SESSION['mapx'];
}

$y=$x;
if (isset($_SESSION['mapy']))
{
    $y=$_SESSION['mapy'];
}

$margin=60;
if (isset($_SESSION['margin']))
{
    $margin=$_SESSION['margin'];
}
;
$x-=$margin*2;
$y-=$margin*2;


$basesize=2;
if (isset($_SESSION['starsize']))
{
    $basesize=$_SESSION['starsize'];
}



$linewidth=1;
if (isset($_SESSION['linewidth']))
{
    $linewidth=$_SESSION['linewidth'];
}

$stars=array();

if (isset($_SESSION['starjson']))
{
    $stars=json_decode($_SESSION['starjson'],true);
}






$im = @imagecreatetruecolor($x+($margin*2), $y+($margin*2))
      or die('Cannot Initialize new GD image stream');
$white_color = imagecolorallocate($im, 255, 255, 255);
$black_color = imagecolorallocate($im, 0, 0, 0);
$line_color = imagecolorallocate($im, 60, 60, 60);
$text_color = imagecolorallocate($im, 90, 90, 255);
$font='/usr/share/fonts/dejavu/DejaVuLGCSansMono.ttf';
#imagealphablending($im, false);
#imagesavealpha($im, true);




$whereclause="";

if (isset($_SESSION['mapregion']))
{
    $whereclause="and mss1.regionid=".$_SESSION['mapregion'];
}
if (isset($_SESSION['mapconstellation']))
{
    $whereclause="and mss1.constellationid=".$_SESSION['mapconstellation'];
}


$dimensionsql='select min(x) minx,min(z) as miny,max(x) maxx, max(z) as maxy from eve.mapSolarSystems mss1 where security !=-0.99';

$stmt = $dbh->prepare($dimensionsql." ".$whereclause);

$stmt->execute();

$row=$stmt->fetchObject();
$maxx=$row->maxx;
$maxy=$row->maxy;
$minx=$row->minx;
$miny=$row->miny;

$scalex=($maxx-$minx)/$x;
$scaley=($maxy-$miny)/$y;
$sql="select solarsystemid,solarsystemname,'255' r,'255' g,'255' b,x,z as y from eve.mapSolarSystems mss1 where security !=-0.99";

$stmt = $dbh->prepare($sql." ".$whereclause);

$stmt->execute();

$colors=array();

while ($row = $stmt->fetchObject()){
    if (!isset($colors[$row->r."#".$row->g."#".$row->b]))
    {
        $colors[$row->r."#".$row->g."#".$row->b]=imagecolorallocate($im,$row->r,$row->g,$row->b);
    }

    $starsize=$basesize;

    $color=$colors[$row->r."#".$row->g."#".$row->b];

    if (isset($stars[$row->solarsystemid]))
    {
        $star=$stars[$row->solarsystemid];
        if (!isset($colors[$star['r']."#".$star['g']."#".$star['b']]))
        {
            $colors[$star['r']."#".$star['g']."#".$star['b']]=imagecolorallocate($im,$star['r'],$star['g'],$star['b']);
        }

        $color=$colors[$star['r']."#".$star['g']."#".$star['b']];
        $starsize=$star['size'];
    }

    imagefilledellipse($im,(($row->x-$minx)/$scalex)+$margin,(($row->y-$miny)/$scaley)+$margin,$starsize,$starsize,$color);
    if ($systemlabel)
    {
        imagettftext($im, 10,0, (($row->x-$minx)/$scalex)+$margin+5,(($row->y-$miny)/$scaley)+$margin-5,$text_color,$font,$row->solarsystemname);
    }
}

$jumpsql="select mss1.x x1,mss1.z y1,mss2.x x2,mss2.z y2 from mapSolarSystemJumps mssj join  mapSolarSystems mss1 on mssj.fromSolarSystemID=mss1.solarSystemID join  mapSolarSystems mss2 on mssj.toSolarSystemID=mss2.solarSystemID";

$stmt = $dbh->prepare($jumpsql." ".$whereclause);

$stmt->execute();

while ($row = $stmt->fetchObject()){
imagelinethick($im,(($row->x1-$minx)/$scalex)+$margin,(($row->y1-$miny)/$scaley)+$margin,(($row->x2-$minx)/$scalex)+$margin,(($row->y2-$miny)/$scaley)+$margin,$line_color,1);


}

if ($regionlabel)
{
    $labelsql="select regionname,x,z as y from mapRegions where x between :minx and :maxx and z between :miny and :maxy";
    $stmt = $dbh->prepare($labelsql);

    $stmt->execute(array(":minx"=>$minx,":miny"=>$miny,":maxx"=>$maxx,":maxy"=>$maxy));

    while ($row = $stmt->fetchObject()){
        imagettftext($im, 10,0, (($row->x-$minx)/$scalex)+$margin,(($row->y-$miny)/$scaley)+$margin,$text_color,$font,$row->regionname);
    }
}


imagepng($im);
imagedestroy($im);





function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    /* this way it works well only for orthogonal lines
    imagesetthickness($image, $thick);
    return imageline($image, $x1, $y1, $x2, $y2, $color);
    */
    if ($thick == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) {
        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}









?>
