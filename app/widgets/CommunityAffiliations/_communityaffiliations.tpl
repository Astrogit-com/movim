{if="$role == 'owner'"}
    <ul class="list active">
        <li onclick="CommunityConfig_ajaxGetAvatar('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">image</i>
            </span>
            <content>
                <p class="normal">{$c->__('page.avatar')}</p>
            </content>
        </li>
        <li onclick="CommunityConfig_ajaxGetConfig('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">settings</i>
            </span>
            <content>
                <p class="normal">{$c->__('communityaffiliation.configuration')}</p>
            </content>
        </li>
        <li onclick="CommunityAffiliations_ajaxGetSubscriptions('{$info->server|echapJS}', '{$info->node|echapJS}', true)">
            <span class="primary icon gray">
                <i class="material-icons">contacts</i>
            </span>
            <content>
                <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
            </content>
        </li>
        <li onclick="CommunityAffiliations_ajaxAffiliations('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">supervisor_account</i>
            </span>
            <content>
                <p class="normal">{$c->__('communityaffiliation.roles')}</p>
            </content>
        </li>
        <li onclick="CommunityAffiliations_ajaxDelete('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">delete</i>
            </span>
            <content>
                <p class="normal">{$c->__('button.delete')}</p>
            </content>
        </li>
    </ul>
{/if}

{if="array_key_exists('owner', $affiliations)"}
    <ul class="list card active">
        <li class="subheader">
            <content>
                <p>{$c->__('communityaffiliation.owners')}</p>
            </content>
        </li>
        {loop="$affiliations['owner']"}
            {$contact = $c->getContact($value['jid'])}
            <li title="{$contact->jid}"
                onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
                {$url = $contact->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble"
                        style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon bubble color {$contact->jid|stringToColor}">
                        {$contact->truename|firstLetterCapitalize}
                    </span>
                {/if}
                <content>
                    <p>{$contact->truename}</p>
                    <p>{$contact->jid}</p>
                </content>
            </li>
        {/loop}
    </ul>
{/if}

{if="array_key_exists('publisher', $affiliations)"}
<ul class="list card active">
    <li class="subheader">
        <content>
            <p>{$c->__('communityaffiliation.publishers')}</p>
        </content>
    </li>
    {loop="$affiliations['publisher']"}
        {$contact = $c->getContact($value['jid'])}
        <li title="{$contact->jid}"
            onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
            {$url = $contact->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble"
                    style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$contact->jid|stringToColor}">
                    {$contact->truename|firstLetterCapitalize}
                </span>
            {/if}
            <content>
                <p>{$contact->truename}</p>
                <p>{$contact->jid}</p>
            </content>
        </li>
    {/loop}
</ul>
{/if}

{if="$subscriptions->isNotEmpty()"}
<ul class="list card active thin">
    <li class="subheader">
        <content>
            <p>{$c->__('communityaffiliation.subscriptions')}</p>
        </content>
    </li>
    {loop="$subscriptions"}
        <li title="{$value->jid}"
            onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
            {if="$value->contact"}
                {$url = $value->contact->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble small"
                        style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon bubble small color {$value->jid|stringToColor}">
                        {$value->contact->truename|firstLetterCapitalize:true}
                    </span>
                {/if}
                <content>
                    <p class="normal">{$value->contact->truename}</p>
                </content>
            {else}
                <span class="primary icon bubble small color {$value->jid|stringToColor}">
                    {$value->jid|firstLetterCapitalize:true}
                </span>
                <content>
                    <p class="normal">{$value->jid}</p>
                </content>
            {/if}
        </li>
    {/loop}
</ul>
{/if}
