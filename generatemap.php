<?
ini_set('memory_limit','800M');
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

$textsize=10;
if (isset($_SESSION['textsize']))
{
    $textsize=$_SESSION['textsize'];
}

$regionsize=10;
if (isset($_SESSION['regionsize']))
{
    $regionsize=$_SESSION['regionsize'];
}

$security=0;
if (isset($_SESSION['security']))
{
    $security=$_SESSION['security'];
}
$font=0;
if (isset($_SESSION['font']))
{
    $font=$_SESSION['font'];
}



$im = @imagecreatetruecolor($x+($margin*2), $y+($margin*2))
      or die('Cannot Initialize new GD image stream');
$star_color = imagecolorallocate($im, 255, 255, 255);
$background = imagecolorallocate($im, 0, 0, 0);
$line_color = imagecolorallocate($im, 60, 60, 60);
$startext_color = imagecolorallocate($im, 90, 90, 255);
$regiontext_color = imagecolorallocate($im, 90, 90, 255);


$fontarray=array('/usr/share/fonts/dejavu/DejaVuLGCSansMono.ttf','/home/web/fuzzwork/htdocs/mapmaker/EveSansNeue-Bold.otf','/home/web/fuzzwork/htdocs/mapmaker/EveSansNeue-Regular.otf');


#$font='/usr/share/fonts/dejavu/DejaVuLGCSansMono.ttf';
$font=$fontarray[$font-1];


if (isset($_SESSION['mapbackground']))
{
    $background=ImageColorAllocateFromHex($im,$_SESSION['mapbackground']);
}
if (isset($_SESSION['maplinecolor']))
{
    $line_color=ImageColorAllocateFromHex($im,$_SESSION['maplinecolor']);
}
if (isset($_SESSION['mapstarcolor']))
{
    $star_color=ImageColorAllocateFromHex($im,$_SESSION['mapstarcolor']);
}
if (isset($_SESSION['mapstartextcolor']))
{
    $startext_color=ImageColorAllocateFromHex($im,$_SESSION['mapstartextcolor']);
}
if (isset($_SESSION['mapregioncolor']))
{
    $regiontext_color=ImageColorAllocateFromHex($im,$_SESSION['mapregioncolor']);
}


imagefill($im,0,0,$background);


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




$jumpsql="select mss1.x x1,mss1.z y1,mss2.x x2,mss2.z y2 from mapSolarSystemJumps mssj join  mapSolarSystems mss1 on mssj.fromSolarSystemID=mss1.solarSystemID join  mapSolarSystems mss2 on mssj.toSolarSystemID=mss2.solarSystemID";
$stmt = $dbh->prepare($jumpsql." ".$whereclause);
$stmt->execute();
while ($row = $stmt->fetchObject()){
imagelinethick($im,(($row->x1-$minx)/$scalex)+$margin,$y-((($row->y1-$miny)/$scaley)-$margin),(($row->x2-$minx)/$scalex)+$margin,$y-((($row->y2-$miny)/$scaley)-$margin),$line_color,1);
}




$sql="select solarsystemid,solarsystemname,x,z as y,red starred, green stargreen,blue starblue from eve.mapSolarSystems mss1 join evesupport.securitycolor on (round(mss1.security,1)=evesupport.securitycolor.level) where security !=-0.99";

$stmt = $dbh->prepare($sql." ".$whereclause);

$stmt->execute();

$colors=array();

while ($row = $stmt->fetchObject()){
    $starsize=$basesize;

    $color=$star_color;

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
    if ($security)
    {    
         if (!isset($colors[$row->starred."#".$row->stargreen."#".$row->starblue]))
         {
            $colors[$row->starred."#".$row->stargreen."#".$row->starblue]=imagecolorallocate($im,$row->starred,$row->stargreen,$row->starblue);
         }
         $color=$colors[$row->starred."#".$row->stargreen."#".$row->starblue];
    }
    imagefilledellipse($im,(($row->x-$minx)/$scalex)+$margin,$y-((($row->y-$miny)/$scaley)-$margin),$starsize,$starsize,$color);
}
$stmt->execute();
if ($systemlabel)
{
while ($row = $stmt->fetchObject())
    {  
        imagettftext($im, $textsize,0, (($row->x-$minx)/$scalex)+$margin+5,$y-((($row->y-$miny)/$scaley)-$margin+5),$startext_color,$font,$row->solarsystemname);
    }
}

if ($regionlabel)
{
    $labelsql="select regionname,x,z as y from mapRegions where x between :minx and :maxx and z between :miny and :maxy";
    $stmt = $dbh->prepare($labelsql);

    $stmt->execute(array(":minx"=>$minx,":miny"=>$miny,":maxx"=>$maxx,":maxy"=>$maxy));

    while ($row = $stmt->fetchObject()){
        imagettftext($im, $regionsize,0, (($row->x-$minx)/$scalex)+$margin,$y-((($row->y-$miny)/$scaley)-$margin),$regiontext_color,$font,$row->regionname);
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


function ImageColorAllocateFromHex ($img, $hexstr)
{
  $hexstr=trim($hexstr,'#');
  $int = hexdec($hexstr);

  return ImageColorAllocate ($img,
         0xFF & ($int >> 0x10),
         0xFF & ($int >> 0x8),
         0xFF & $int);
} 






?>
