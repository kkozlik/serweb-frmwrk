{* Smarty *}
{* 
 *  $Id: $ 
 *
 *}

{include file='_head.tpl'}

{if $name}
<h1>Hello {$name}!</h1>
{else}
<h1>Hello world!</h1>
{/if}


    <div class="swForm">
    {$form.start}

    Enter your name: {$form.hello_world_name}<br />
    {$form.okey}
    {$form.finish}
    </div>

<br>
{include file='_tail.tpl'}
