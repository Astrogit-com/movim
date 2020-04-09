<div id="adminsessions" class="tabelem" title="{$c->__("adminsessions.title")}">
    <ul class="list divided middle flex active">
        <li class="subheader block large">
            <div>
                <p>{$c->__('adminsessions.title')} <span class="second">{$sessions|count}</a></p>
            </div>
        </li>
        {loop="$sessions"}
            {$user = $c->getContact($value->user)}
            <li class="block" onclick="MovimUtils.redirect('{$c->route('contact', $user->id)}')">
                {$url = $user->getPhoto()}
                {if="$url"}
                    <span class="primary icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$user->id|stringToColor}">
                        <i class="material-icons">person</i>
                    </span>
                {/if}
                <div>
                    <p class="line" title="{$user->id}">
                        {$user->truename} <span class="second">{$user->id}</span>
                    </p>
                    <p>
                        {$value->created_at|strtotime|prepareDate}
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
</div>
