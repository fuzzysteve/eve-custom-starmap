<?php
ini_set('memory_limit', '200M');
require_once('db.inc.php');

$x=800;

$y=$x;

$margin=60;
$x-=$margin*2;
$y-=$margin*2;
$transparent=0;

$basesize=2;

$linewidth=1;



$im = @imagecreatetruecolor($x+($margin*2), $y+($margin*2))
      or die('Cannot Initialize new GD image stream');

if ($transparent) {
    imagealphablending($im, false);
    imagesavealpha($im, true);
}
$star_color = imagecolorallocate($im, 55, 55, 55);
$background = imagecolorallocatealpha($im, 0, 0, 0, $transparent);
$line_color = imagecolorallocate($im, 60, 60, 60);
$cyno_color = imagecolorallocate($im, 0, 255, 255);
$regiontext_color = imagecolorallocate($im, 90, 90, 255);

imagefill($im, 0, 0, $background);


$dimensionsql='select min(x) minx,
    min(z) as miny,
    max(x) maxx,
    max(z) as maxy 
    from eve.mapSolarSystems mss1 where security !=-0.99';

$stmt = $dbh->prepare($dimensionsql);

$stmt->execute();

$row=$stmt->fetchObject();
$maxx=$row->maxx;
$maxy=$row->maxy;
$minx=$row->minx;
$miny=$row->miny;

$scalex=($maxx-$minx)/$x;
$scaley=($maxy-$miny)/$y;



if ($linewidth>0) {
    $jumpsql="select mss1.x x1,
        mss1.z y1,
        mss2.x x2,
        mss2.z y2 
        from mapSolarSystemJumps mssj 
        join  mapSolarSystems mss1 on mssj.fromSolarSystemID=mss1.solarSystemID 
        join  mapSolarSystems mss2 on mssj.toSolarSystemID=mss2.solarSystemID";
    $stmt = $dbh->prepare($jumpsql);
    $stmt->execute();
    while ($row = $stmt->fetchObject()) {
        imagelinethick(
            $im,
            (($row->x1-$minx)/$scalex)+$margin,
            $y-((($row->y1-$miny)/$scaley)-$margin),
            (($row->x2-$minx)/$scalex)+$margin,
            $y-((($row->y2-$miny)/$scaley)-$margin),
            $line_color,
            $linewidth
        );
    }
}

$sql="select solarsystemid,
    solarsystemname,
    x,
    z as y,
    red starred, 
    green stargreen,
    blue starblue 
    from eve.mapSolarSystems mss1 
    join evesupport.securitycolor on (round(mss1.security,1)=evesupport.securitycolor.level) where security !=-0.99";

$stmt = $dbh->prepare($sql);

$stmt->execute();

$colors=array();

while ($row = $stmt->fetchObject()) {
    imagefilledellipse(
        $im,
        (($row->x-$minx)/$scalex)+$margin,
        $y-((($row->y-$miny)/$scaley)-$margin),
        $basesize,
        $basesize,
        $star_color
    );
}


$sql="select cyno,system,LPAD(m,2,'0') m,LPAD(d,2,'0') d, LPAD(h,2,'0') h,x,z as y from evesupport.cyno order by m,d,h";

$stmt = $dbh->prepare($sql);

$stmt->execute();

$frame=cloneImg($im);

$start=1;
$font='/home/web/fuzzwork/htdocs/mapmaker/EveSansNeue-Bold.otf';
$fontsize=20;
while ($row = $stmt->fetchObject()) {
    imagefilledellipse(
        $frame,
        (($row->x-$minx)/$scalex)+$margin,
        $y-((($row->y-$miny)/$scaley)-$margin),
        $row->cyno,
        $row->cyno,
        $cyno_color
    );
    if ($start) {
        $start=0;
        $month=$row->m;
        $day=$row->d;
        $hour=$row->h;
    } elseif (!(($month==$row->m) and ($day==$row->d) and ($hour==$row->h))) {
        imagettftext($frame, $fontsize, 0, 400, 780, $regiontext_color, $font, $month."-".$day."-".$hour);
        imagegif($frame, 'frame-'.$month."-".$day."-".$hour.".gif");
        echo $month."-".$day."-".$hour."\n";
        $frame=cloneImg($im);
        $month=$row->m;
        $day=$row->d;
        $hour=$row->h;
    }
}




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
        return imagefilledrectangle(
            $image,
            round(min($x1, $x2) - $t),
            round(min($y1, $y2) - $t),
            round(max($x1, $x2) + $t),
            round(max($y1, $y2) + $t),
            $color
        );
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


function ImageColorAllocateFromHex($img, $hexstr, $alpha = 0)
{
    $hexstr=trim($hexstr, '#');
    $int = hexdec($hexstr);

    return ImageColorAllocateAlpha(
        $img,
        0xFF & ($int >> 0x10),
        0xFF & ($int >> 0x8),
        0xFF & $int,
        $alpha
    );
}

function cloneImg ($original)
{
    $x=800;

    $y=$x;

    $margin=60;
    $x-=$margin*2;
    $y-=$margin*2;
    $copy = @imagecreatetruecolor($x+($margin*2), $y+($margin*2));
    imagecopy($copy, $original, 0, 0, 0, 0, $x+($margin*2), $y+($margin*2));
    return $copy;
}
