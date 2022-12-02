{* Smarty *}
{* 
 *	Smarty template displaying error messages
 *
 *}

{foreach from=$errors item='row' name='errors'}
{if $smarty.foreach.errors.first}
	<div class="errorMsgWrapper">
    <table width="70%" cellpadding=4 cellspacing=0 border=0 class="errtbl">
        <tr>
            <td class="errmsg" style="font-weight: bold">There was an error:</td>
        </tr>
{/if}	
        <tr>
            <td class="errmsg" style="font-style: italic">{$row|escape|nl2br}</td>
        </tr>
{if $smarty.foreach.errors.last}
    </table>
    </div>
    <br>
{/if}	
{/foreach}
