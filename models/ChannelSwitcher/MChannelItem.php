<?php
namespace Rehike\Model\ChannelSwitcher;

use Rehike\i18n;
use Rehike\Util\ParsingUtils;
use Rehike\TemplateFunctions as TF;

class MChannelItem
{
    public bool $selected = false;
    public string $ucid;
    public string $url;
    public string $avatar;
    public string $title;
    public string $subscriberCountText;

    
    public function __construct(object $data, ?string $next)
    {
        $i18n = i18n::getNamespace("channel_switcher");

        $this->selected = $data->isSelected;
        $this->avatar = ParsingUtils::getThumb($data->accountPhoto, 56);
        $this->title = ParsingUtils::getText(@$data->accountName);

        $this->subscriberCountText = $data->hasChannel
            ? ParsingUtils::getText(@$data->accountByline)
            : $i18n->ownerAccountNoChannel;

        $tokenRoot = $data->serviceEndpoint->selectActiveIdentityEndpoint->supportedTokens;

        foreach($tokenRoot as $token)
        {
            if (isset($token->offlineCacheKeyToken))
            {
                $this->ucid = "UC" . $token->offlineCacheKeyToken->clientCacheKey;
            }
            elseif (isset($token->accountSigninToken))
            {
                $this->url = $token->accountSigninToken->signinUrl;
            }
        }

        // Apply next URL param to switch links
        if (!is_null($next))
        {
            $this->url = preg_replace("/(?<=\?|&)next=(.*?)(?=&|$)/", "next=$next", $this->url);
        }
    }
}