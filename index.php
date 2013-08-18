<?php

$x=800;
$y=800;
$margin=20;
$region="";
$constellation="";
$systemlabel=0;
$regionlabel=0;
$stardisplay="";

if (isset($_POST['submit']))
{
    session_start();

    if (isset($_POST['x']) && is_numeric($_POST['x']) && $_POST['x']<3000)
    {
        $x=$_POST['x'];
        $_SESSION['mapx']=$_POST['x'];
    }
    if (isset($_POST['y']) && is_numeric($_POST['y']) && $_POST['y']<3000)
    {
        $y=$_POST['y'];
        $_SESSION['mapy']=$_POST['y'];
    }

    if (isset($_POST['margin']) && is_numeric($_POST['margin']) && $_POST['margin']<3000)
    {
        $margin=$_POST['margin'];
        $_SESSION['margin']=$_POST['margin'];
    }

    if (isset($_POST['region']) && is_numeric($_POST['region']))
    {
        $region=$_POST['region'];
        $_SESSION['mapregion']=$_POST['region'];
    }
    else
    {
        unset($_SESSION['mapregion']);
    }
    if (isset($_POST['constellation']) && is_numeric($_POST['constellation']))
    {
        $constellation=$_POST['constellation'];
        $_SESSION['mapconstellation']=$_POST['constellation'];
    }
    else
    {
        unset($_SESSION['mapconstellation']);
    }

    if (isset($_POST['constellation']) && is_numeric($_POST['constellation']))
    {
        $constellation=$_POST['constellation'];
        $_SESSION['mapconstellation']=$_POST['constellation'];
    }
 
    if (isset($_POST['systemlabel']) && is_numeric($_POST['systemlabel']) && $_POST['systemlabel']<2)
    {
        $systemlabel=$_POST['systemlabel'];
        $_SESSION['systemlabel']=$_POST['systemlabel'];
    }
    if (isset($_POST['regionlabel']) && is_numeric($_POST['regionlabel']) && $_POST['regionlabel']<2)
    {
        $regionlabel=$_POST['regionlabel'];
        $_SESSION['regionlabel']=$_POST['regionlabel'];
    }

    if (isset($_POST['stars']))
    {
        $stars=array();
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $_POST['stars']) as $line){
             if (preg_match("/(\d+),(\d+),(\d+),(\d+),(\d+)/",$line,$matches)){
                 list($total,$starid,$r,$b,$g,$size)=$matches;
                 $stardisplay.="$starid,$r,$b,$g,$size\n";
                 $stars[$starid]=array("r"=>$r,"g"=>$g,"b"=>$b,"size"=>$size);
             }
             $_SESSION['starjson']=json_encode($stars);
        } 
    }
    else
    {
        unset($_SESSION['starjson']);
    }

$starsize=2;
    if (isset($_POST['starsize']) && is_numeric($_POST['starsize']) && $_POST['starsize']<3000)
    {
        $starsize=$_POST['starsize'];
        $_SESSION['starsize']=$_POST['starsize'];
    }
$linewidth=1;
    if (isset($_POST['linewidth']) && is_numeric($_POST['linewidth']) && $_POST['linewidth']<3000)
    {
        $linewidth=$_POST['linewidth'];
        $_SESSION['linewidth']=$_POST['linewidth'];
    }

}


?>

<html>
<head><title>Create Starmap</title></head>
<style>
</style>
<body>
<p>These maps are not stored, being generated from your session. Save them to your computer, then upload them to imgur or another host</p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<table>
<tr><td><label for='x'>X</label></td><td><input class="input" type=text id="x" name=x value=<?php echo $x; ?>></td><td></td></tr>
<tr><td><label for='y'>Y</label></td><td><input class="input" type=text id="y" name=y value=<?php echo $y; ?>></td><td></td></tr>
<tr><td><label for='starsize'>Star Size</label></td><td><input class="input" tstarsizepe=text id="starsize" name=starsize value=<?php echo $starsize; ?>></td><td></td></tr>
<tr><td><label for='linewidth'>Line Width</label></td><td><input class="input" tlinewidthpe=text id="linewidth" name=linewidth value=<?php echo $linewidth; ?>></td><td></td></tr>
<tr><td><label for='margin'>Margin</label></td><td><input class="input" name=margin type=text id="margin" value=<?php echo $margin?>></td><td></td></tr>
<tr><td><label for="region">Region</label></td><td><input  class="input" type=text id="region" name="region" value=<?php echo $region?>></td><td> Leave blank for no region restriction</td></tr>
<tr><td><label for="constellation">Constellation</label></td><td><input class="input" type=text id="constellation" name=constellation value=<?php echo $constellation?>></td><td> Leave blank for no Constellation restriction</td></tr>
<tr><td>System labels</td><td><select name="systemlabel"><option value=0 <? if ($systemlabel==0){ echo "selected"; }?>>Off</option> <option value=1 <? if ($systemlabel==1){ echo "selected"; }?>>On</input></select></td><td></tr>
<tr><td>Region labels</td><td><select name="regionlabel"><option value=0 <? if ($regionlabel==0){ echo "selected"; }?>>Off</option> <option value=1 <? if ($regionlabel==1){ echo "selected"; }?>>On</input></select></td><td></tr>
<tr><td>Custom Highlights</td><td><textarea name="stars"><?echo $stardisplay; ?></textarea></td><td>starid,red,green,blue,size</td></tr>
</table>
<input type="submit" name="submit" value="Create Map" id="submit">
</form>
<hr>
<img src="generatemap.php">
</body>
</html>
