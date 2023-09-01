<div align="left">
  <form action="" id="form" method='post'>
	<table width="100%" style="border-top:none;border-left:none;border-right:none;">
		<tr>
			<td><input type="submit" name="do_all" value="do_all"></td>
			<td ><input type="submit" name="show_turn" value="show_turn"></td>
			<td >turn->{$turn_counter}</td>
			<td width="100%">
				<input type="submit" name="reset" value="reset">
				{if $clearForm == 'checked'}
					<input type="checkbox" name='clearForm' value='true' checked="">clear boxs on reset?
				{else}
					<input type="checkbox" name='clearForm' value='true'>clear boxs on reset?
				{/if}
			<td/>
			<td >{$cache_size}<-rough_cache_size_at_load</td>
			<td ><input type="submit" name="refresh" value="refresh"></td>
		<tr>
	</table>
	
	<br>
	<br>
	<br>
	<input type="submit" name="addUserButton" value='addUser'>
	=> username,team|username,team|.....<br>
	<textarea rows='6' cols='125' name = "addUserForm" form='form'>{$addUser}</textarea>
	<br>
	<br>
	<br>
	
	<!--
	<input type ="submit" name="doJutsuButton" value="doJutsu">
	=> select a jutsu to be used here.
	<select name="owningUserForm" form="form">{$userList}</select>
	<select name="doJutsuForm" form="form">{$doJutsuOptions}</select>
	<select name="weapon1Form" form="form">{$weaponList}</select>
	<select name="weapon2Form" form="form">{$weaponList}</select>
	<select name="targetUserForm" form="form">{$userList}</select>
	<br>
	<br>
	<br>
	-->

	<input type="submit" name="addTagsButton" value="addTags">
	=> &#123;universalField>value&#125;tagname:(field>value;field>(value,value,value);)~target_username,owner_username,tagorigin,equipment_id,effect_level|.....<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	you can add more tags per add by adding tagname and fields again with another "~"<br>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		
	~target_username~owner_username~equipment_id will all be added by the system and are not part of a normal tag description<br>
	
	<textarea rows='12' cols='125' name = "addTagsForm" form='form'>{$addTags}</textarea>
	<br>
	<br>
	<br>

	<br>
	<br>
	<br>
	<input type="submit" name="addLocationTagsButton" value='addLocationTags'>
	=> &#123;universalField>value&#125;tagname:(field>value)~tagname:(field>value)
	<br>
	<input type="checkbox" name='addLocationTagsOverride' value='true'>override existing location tags
	<br>
	<textarea rows='6' cols='125' name = "addLocationTagsForm" form='form'>{$addLocationTags}</textarea>
	<br>
	<br>
	<br>

	<input type='submit' name='removeUserButton' value='removeUser'>
	=> username|username|.....<br>
	<textarea rows='6' cols='125' name = "removeUserForm" form='form'>{$removeUser}</textarea>
	<br>
	<br>
	<br>

	<input type='submit' name='removeEquipmentByIdButton' value='removeEquipmentById'>
	=> equipment_id|equipment_id|.....<br>
	<textarea rows='3' cols='125' name = "removeEquipmentByIdForm" form='form'>{$removeEquipmentById}</textarea>
	<br>
	<br>
	<br>

  </form>
</div>
<br>
<br>
<div align="left">
	{$result}
</div>