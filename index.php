<?php

$x=800;
$y=800;
$margin=20;
$region="";
$constellation="";
$security="";
$systemlabel=0;
$regionlabel=0;
$stardisplay="";
$bgcolor="#000000";
$starcolor="#ffffff";
$linecolor="#666666";
$starsize=2;
$textsize=10;
$regionsize=10;
$linewidth=1;
$font=1;
$transparent=0;
$startextcolor="#0000ff";
$regiontextcolor="#0000ff";

if (isset($_POST['submit']))
{
    session_start();

    if (isset($_POST['x']) && is_numeric($_POST['x']) && $_POST['x']<=3000)
    {
        $x=$_POST['x'];
        $_SESSION['mapx']=$_POST['x'];
    }
    if (isset($_POST['y']) && is_numeric($_POST['y']) && $_POST['y']<=3000)
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
    
    if (isset($_POST['security']) && is_numeric($_POST['security']))
    {
        $security=$_POST['security'];
        $_SESSION['security']=$_POST['security'];
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
                 list($total,$starid,$r,$g,$b,$size)=$matches;
                 $stardisplay.="$starid,$r,$g,$b,$size\n";
                 $stars[$starid]=array("r"=>$r,"g"=>$g,"b"=>$b,"size"=>$size);
             }
             $_SESSION['starjson']=json_encode($stars);
        } 
    }
    else
    {
        unset($_SESSION['starjson']);
    }

    if (isset($_POST['starsize']) && is_numeric($_POST['starsize']) && $_POST['starsize']<3000)
    {
        $starsize=$_POST['starsize'];
        $_SESSION['starsize']=$_POST['starsize'];
    }
    if (isset($_POST['textsize']) && is_numeric($_POST['textsize']) && $_POST['textsize']<100)
    {
        $textsize=$_POST['textsize'];
        $_SESSION['textsize']=$_POST['textsize'];
    }
    
    if (isset($_POST['regionsize']) && is_numeric($_POST['regionsize']) && $_POST['regionsize']<100)
    {
        $regionsize=$_POST['regionsize'];
        $_SESSION['regionsize']=$_POST['regionsize'];
    }

    if (isset($_POST['font']) && is_numeric($_POST['font']) && $_POST['font']<4)
    {
        $font=$_POST['font'];
        $_SESSION['font']=$_POST['font'];
    }


    if (isset($_POST['linewidth']) && is_numeric($_POST['linewidth']) && $_POST['linewidth']<3000)
    {
        $linewidth=$_POST['linewidth'];
        $_SESSION['linewidth']=$_POST['linewidth'];
    }
    
    if (isset($_POST['background']) && preg_match('/#[0-9A-Fa-f]{6}/',$_POST['background']))
    {
        $bgcolor=$_POST['background'];
        $_SESSION['mapbackground']=$bgcolor;
    }
    if (isset($_POST['starcolor']) && preg_match('/#[0-9A-Fa-f]{6}/',$_POST['starcolor']))
    {
        $starcolor=$_POST['starcolor'];
        $_SESSION['mapstarcolor']=$starcolor;
    }
    if (isset($_POST['linecolor']) && preg_match('/#[0-9A-Fa-f]{6}/',$_POST['linecolor']))
    {
        $linecolor=$_POST['linecolor'];
        $_SESSION['maplinecolor']=$linecolor;
    }
    if (isset($_POST['startextcolor']) && preg_match('/#[0-9A-Fa-f]{6}/',$_POST['startextcolor']))
    {
        $startextcolor=$_POST['startextcolor'];
        $_SESSION['mapstartextcolor']=$startextcolor;
    }
    if (isset($_POST['regioncolor']) && preg_match('/#[0-9A-Fa-f]{6}/',$_POST['regioncolor']))
    {
        $regiontextcolor=$_POST['regioncolor'];
        $_SESSION['mapregioncolor']=$regiontextcolor;
    }
    if (isset($_POST['transparent']) && is_numeric($_POST['transparent']))
    {
        $transparent=$_POST['transparent'];
        if ($transparent)
        {
            $_SESSION['transparent']=127;
        }
        else
        {
            $_SESSION['transparent']=0;
        }


 
    }



}
echo $transparent;

?>

<html>
<head><title>Create Starmap</title>
  <link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

  <script src='spectrum.js'></script>
  <link rel='stylesheet' href='spectrum.css' />
<script>
$(document).ready( function() {
  $('#background').spectrum({});
  $('#starcolor').spectrum({});
  $('#linecolor').spectrum({});
  $('#startextcolor').spectrum({});
  $('#regioncolor').spectrum({});
});
</script>

<?php include('/home/web/fuzzwork/htdocs/bootstrap/header.php'); ?>
</head>
<body>
<?php include('/home/web/fuzzwork/htdocs/menu/menubootstrap.php'); ?>
<div class="container">


<p>These maps are not stored, being generated from your session. Save them to your computer, then upload them to imgur or another host</p>
<p> Get the source from <a href="https://github.com/fuzzysteve/eve-custom-starmap">https://github.com/fuzzysteve/eve-custom-starmap</a></p>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<table>
<tr><td><label for='x'>X</label></td><td><input class="input" type=text id="x" name=x value=<?php echo $x; ?>></td><td></td></tr>
<tr><td><label for='y'>Y</label></td><td><input class="input" type=text id="y" name=y value=<?php echo $y; ?>></td><td></td></tr>
<tr><td><label for='starsize'>Star Size</label></td><td><input class="input" type=text id="starsize" name=starsize value=<?php echo $starsize; ?>></td><td></td></tr>
<tr><td><label for='textsize'>Text Size</label></td><td><input class="input" type=text id="textsize" name=textsize value=<?php echo $textsize; ?>></td><td></td></tr>
<tr><td><label for='regionsize'>Region Text Size</label></td><td><input class="input" type=text id="regionsize" name=regionsize value=<?php echo $regionsize; ?>></td><td></td></tr>
<tr><td><label for='linewidth'>Line Width</label></td><td><input class="input" type=text id="linewidth" name=linewidth value=<?php echo $linewidth; ?>></td><td></td></tr>
<tr><td><label for='margin'>Margin</label></td><td><input class="input" name=margin type=text id="margin" value=<?php echo $margin?>></td><td></td></tr>
<tr><td><label for="region">Region</label></td><td><input  class="input" type=text id="region" name="region" value=<?php echo $region?>></td><td> Leave blank for no region restriction</td></tr>
<tr><td><label for="constellation">Constellation</label></td><td><input class="input" type=text id="constellation" name=constellation value=<?php echo $constellation?>></td><td> Leave blank for no Constellation restriction</td></tr>
<tr><td>System labels</td><td><select name="systemlabel"><option value=0 <? if ($systemlabel==0){ echo "selected"; }?>>Off</option> <option value=1 <? if ($systemlabel==1){ echo "selected"; }?>>On</input></select></td><td></tr>
<tr><td>Region labels</td><td><select name="regionlabel"><option value=0 <? if ($regionlabel==0){ echo "selected"; }?>>Off</option> <option value=1 <? if ($regionlabel==1){ echo "selected"; }?>>On</input></select></td><td></tr>
<tr><td>Color by Security Status</td><td><select name="security"><option value=0 <? if ($security==0){ echo "selected"; }?>>Off</option> <option value=1 <? if ($security==1){ echo "selected"; }?>>On</input></select></td><td></tr>
<tr><td>Font</td><td><select name="font"><option value=1 <? if ($font==1){ echo "selected"; }?>>Font 1</option> <option value=2 <? if ($font==2){ echo "selected"; }?>>Font 2</input><option value=3 <? if ($font==3){ echo "selected"; }?>>Font 3</input></select></td><td></tr>
<tr><td>Custom Highlights</td><td><textarea name="stars"><?echo $stardisplay; ?></textarea></td><td>starid,red,green,blue,size</td></tr>
<tr><td>Background Colour</td><td><input type="text" id="background" name="background" value="<?php echo $bgcolor  ?>"></td><td>Transparent<select name="transparent"><option value=0>no</option><option value=127 <? if ($transparent==127){ echo "selected"; }?>>yes</option></td></tr>
<tr><td>Star Colour</td><td><input type="text" id="starcolor" name="starcolor" value="<?php echo $starcolor  ?>"></td><td></td></tr>
<tr><td>Line Colour</td><td><input type="text" id="linecolor" name="linecolor" value="<?php echo $linecolor  ?>"></td><td></td></tr>
<tr><td>Star Label Colour</td><td><input type="text" id="startextcolor" name="startextcolor" value="<?php echo $startextcolor  ?>"></td><td></td></tr>
<tr><td>Region Label Colour</td><td><input type="text" id="regioncolor" name="regioncolor" value="<?php echo $regiontextcolor  ?>"></td><td></td></tr>
</table>
<input type="submit" name="submit" value="Create Map" id="submit">
</form>
<hr>
<img src="generatemap.php">
</div>
<?php include('/home/web/fuzzwork/htdocs/bootstrap/footer.php'); ?>

</body>
</html>
