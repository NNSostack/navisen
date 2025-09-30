<?php
$embeds = array();

$embeds['/ateste']['json'] = <<<JSON0
{
	"width": 425
}
JSON0;
$embeds['/ateste']['content'] = <<<EMB0
<div id="content" class="content teste">
Test embed!
<script type="application/javascript">
console.log('A Test E');
</script>
</div>
EMB0;

$embeds['/maps']['json'] = <<<JSON1
{
	"width": 425
}
JSON1;
$embeds['/maps']['content'] = <<<EMB1
<div id="content" class="content maps">
<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/?ie=UTF8&amp;ll=55.670143,12.081306&amp;spn=0.240473,0.617294&amp;t=h&amp;z=11&amp;output=embed"></iframe><br />
<small><a href="https://maps.google.com/?ie=UTF8&amp;ll=55.670143,12.081306&amp;spn=0.240473,0.617294&amp;t=h&amp;z=11&amp;source=embed" style="color:#0000FF;text-align:left">Vis stort kort</a></small>
</div>
EMB1;

$embeds['/storify']['json'] = <<<JSON2
{
	"height": 750
}
JSON2;
$embeds['/storify']['content'] = <<<EMB2
<div id="content" class="content storify">
<iframe src="//storify.com/euronews/live-updates-malaysia-plane-missing/embed" width="100%" height=750 frameborder=no allowtransparency=true></iframe>
<script src="//storify.com/euronews/live-updates-malaysia-plane-missing.js"></script>
<noscript>[<a href="//storify.com/euronews/live-updates-malaysia-plane-missing" target="_blank">View the story "(Live) MALAYSIA: New press conference on MH370 missing plane" on Storify</a>]</noscript>
</div>
EMB2;

$embeds['/poll']['json'] = <<<JSON3
{

}
JSON3;
$embeds['/poll']['content'] = <<<EMB3
<div id="content" class="content poll">
<form method="post" action="http://poll.pollcode.com/92444142">
<table border="0" width="175" bgcolor="EEEEEE" cellspacing="2" cellpadding="0">
<tr><td colspan="2"><font face="Verdana" size="2" color="000000"><b>Er det fedt at embedde?</b></font></td></tr>
<tr>
	<td width="5"><input type="radio" name="answer" value="1" id="92444142answer1"></td>
	<td><font face="Verdana" size="2" color="000000"><label for="92444142answer1">Ja</label></font></td>
</tr>
<tr>
	<td width="5"><input type="radio" name="answer" value="2" id="92444142answer2"></td>
	<td><font face="Verdana" size="2" color="000000"><label for="92444142answer2">Nej</label></font></td>
</tr>
<tr>
	<td colspan=2><center><input type="submit" value=" Vote ">&nbsp;&nbsp;<input type="submit" name="view" value=" View "></center></td>
</tr>
<tr>
	<td colspan=2 align=right><font face="Verdana" size="1" color="000000">pollcode.com <a href="http://pollcode.com/"><font face="Verdana" size="1" color="000000">free polls</font></a>&nbsp;</font></td>
</tr>
</table>
</form>
</div>
EMB3;

$embeds['/datawrapper']['json'] = <<<JSON4
{
	"width": 600,
	"height": 400
}
JSON4;
$embeds['/datawrapper']['content'] = <<<EMB4
<div id="content" class="content datawrapper">
<iframe src="http://cf.datawrapper.de/haKVc/1/" frameborder="0" allowtransparency="true" allowfullscreen="allowfullscreen" webkitallowfullscreen="webkitallowfullscreen" mozallowfullscreen="mozallowfullscreen" oallowfullscreen="oallowfullscreen" msallowfullscreen="msallowfullscreen" width="600" height="400"></iframe>
</div>
EMB4;

$embeds['/mapbox']['json'] = <<<JSON5
{
	"width": "100%",
	"height": "500px"
}
JSON5;
$embeds['/mapbox']['content'] = <<<EMB5
<div id="content" class="content mapbox">
<iframe width='100%' height='500px' frameBorder='0' src='http://a.tiles.mapbox.com/v3/makaeb.hi676a0j/attribution,zoompan,zoomwheel,geocoder,share.html'></iframe>
</div>
EMB5;
