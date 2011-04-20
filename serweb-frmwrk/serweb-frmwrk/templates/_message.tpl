{* Smarty *}
{* 
 *	Smarty template displaying status messages
 *
 *}

{foreach from=$message item='row' key='key' name='messages'}
{if $smarty.foreach.messages.first}
   	<div class="statusMsgWrapper">
	<table class="statustbl" width="70%">
{/if}
	<tr><td class="statusmsg">{$row.long|escape}</td></tr>
{if $smarty.foreach.messages.last}
	</table>
	</div>
{/if}
{/foreach}

