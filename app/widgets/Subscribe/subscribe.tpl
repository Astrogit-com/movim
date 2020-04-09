<div id="subscribe">
    <ul class="list thick">
        <li>
            <div>
                <p>{$c->__('subscribe.title')}</p>
                <p>{$c->__('subscribe.info')}</p>
            </div>
        </li>
    </ul>

    <ul class="list card active flex thick">
        {if="!empty($config->xmppdomain)"}
            <li
                class="block large"
                onclick="MovimUtils.redirect('{$c->route('accountnext', [$config->xmppdomain, false])}')">
                <span class="primary icon bubble color {$config->xmppdomain|stringToColor}">
                    {$config->xmppdomain|firstLetterCapitalize}
                </span>
                <div>
                    <p>
                        {if="!empty($config->xmppcountry)"}
                            <span class="info">
                                <img
                                    class="flag"
                                    title="{$config->xmppcountry}"
                                    alt="{$config->xmppc}"
                                    src="{$c->flagPath($config->xmppcountry)}"/>
                            </span>
                        {/if}
                        {$config->xmppdomain}
                    </p>
                    {if="!empty($config->xmppdescription)"}
                    <p>
                        {$config->xmppdescription}<br />
                    </p>
                    {/if}
                </div>
            </li>
        {/if}
        {loop="$servers"}
        <li
            class="block"
            onclick="MovimUtils.redirect('{$c->route('accountnext', [$value->domain, false])}')">
            <span class="primary icon bubble color {$value->description|stringToColor}">
                {if="isset($value->checked) && $value->checked"}
                    <i class="material-icons">star</i>
                {else}
                    {$value->domain|firstLetterCapitalize}
                {/if}
            </span>
            <div>
                <p>
                    <span class="info">
                    <img
                        class="flag"
                        title="{$value->geo_country}"
                        alt="{$value->geo_country}"
                        src="{$c->flagPath($value->geo_country)}"/>
                    </span>
                    {$value->domain}
                </p>
                <p>
                    {$value->description}<br />
                </p>
            </div>
        </li>
        {/loop}
    </ul>
    <ul class="list thick">
        <li class="block">
            <div>
                <p></p>
                <p>{$c->__('subscribe.server_question')}</p>
                <p>
                    {$c->__('subscribe.server_contact')} • <a href="https://movim.eu/">https://movim.eu/</a>
                </p>
            </div>
        </li>
    </ul>
</div>
