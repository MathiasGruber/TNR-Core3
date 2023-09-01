<div align="center"><br>
  	<form name="form1" method="post" action="">
  		<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="3" align="center" style="border-top:none;" class="subHeader">Forbidden Peak </td>
    	</tr>
		<tr>
			<td colspan="3" align="center">
				<p>Scroll in pouch, you quietly scale the mountain side. Doubts  slowly creep their way into your mind. If the rumors are true, then there  should be an elder rogue ninja atop this mountain. He&rsquo;s the one that would be  able to teach you how to perform this jutsu. However, this jutsu is forbidden for a reason, and this man <em>is</em> an outlaw. 
				Who is to say that he won&rsquo;t merely cut you up and take your money?
				After all you are in a remote location; no one would hear you scream.</p>
				<p>Swallowing hard, you continue to climb. Turning back now  would prove nothing. You make your way to the top of the mountain; your eyes  scan the summit. In the distance you see a figure. His back in turned to you,  so all you can see if his shoulder length straggly salt and pepper hair along  with his massive back. </p>
				<p>&ldquo;So yet again, another ninja seeks greater power,&rdquo; The owner  of the voice starts to get up and turn to you. You are frozen in place as your  eyes fall upon his face. The scars of a warrior laid upon his head, so many so  that even if his immense size didn&rsquo;t already frighten you, this would. &ldquo;Many  have scaled this mountain looking to achieve greatness by way of a forbidden  technique. I have the ability to teach you this jutsu. I&rsquo;ll let you in on the  secrets of its power, but I&rsquo;ll only warn you once. Do not let this power go to  your head. 
				If you are not careful, it will be your demise&hellip; Can you handle the  responsibility?&rdquo; </p>
				<p>With a moment of hesitation you find yourself nodding your  head, fear keeping you from speaking the actually words. Your eyes widen as he  moves towards you, and holds out his hand. &ldquo;Give me the scroll&hellip; we&rsquo;ll begin  training immediately.&rdquo;</p>
			</td>
		</tr>
    	<tr>
			<td colspan="3" align="center">
			{if $item != "0 rows"}
				<select name="scroll_id">
				{for $i = 0 to ($item|@count)-1}
					<option value="{$item[$i]['id']}">{$item[$i]['name']}</option>
				{/for}
					</select>
					</td>
				</tr>
				<tr>
					<td colspan="3" align="center" style="padding:3px;"><input type="submit" name="Submit" value="Submit"></td>
				</tr>
			{else}
				<i>You do not have any jutsu scrolls</i></td></tr>
			{/if}
		</table>
	</form><br><br>
</div