<?php

namespace Rehike;

use Rehike\ConfigManager\ConfigManager;
use YukisCoffee\PropertyAtPath;

/**
 * Implements the Rehike-specific portions of the
 * config manager system.
 * 
 * @author Taniko Yamamoto <kiraicecreamm@gmail.com>
 * @author The Rehike Maintainers
 */
class RehikeConfigManager extends ConfigManager
{
    public static array $defaultConfig = [
        "appearance" => [
            "modernLogo" => true,
            "uploadButtonType" => "MENU",
            "largeSearchResults" => true,
            "swapSearchViewsAndDate" => false,
            "showVersionInFooter" => true,
            "usernamePrepends" => false,
            "useRyd" => true,
            "noViewsText" => false,
            "movingThumbnails" => true,
            "cssFixes" => true,
            "watchSidebarDates" => false,
            "watchSidebarVerification" => false,
            "oldBestOfYouTubeIcons" => false
        ],
        "experiments" => [
            "useSignInV2" => false,
            "disableSignInOnHome" => false
        ],
        "advanced" => [
            "enableDebugger" => false
        ],
        "hidden" => [
            "securityIgnoreWindowsServerRunningAsSystem" => false,
            "disableRehike" => false
        ]
    ];

    public static array $types = [
        "appearance" => [
            "modernLogo" => "bool",
            "uploadButtonType" => "enum",
            "largeSearchResults" => "bool",
            "swapSearchViewsAndDate" => "bool",
            "showVersionInFooter" => "bool",
            "usernamePrepends" => "bool",
            "useRyd" => "bool",
            "noViewsText" => "bool",
            "movingThumbnails" => "bool",
            "cssFixes" => "bool",
            "watchSidebarDates" => "bool",
            "watchSidebarVerification" => "bool",
            "oldBestOfYouTubeIcons" => "bool"
        ],
        "experiments" => [
            "useSignInV2" => "bool",
            "disableSignInOnHome" => "bool"
        ],
        "advanced" => [
            "enableDebugger" => "bool"
        ],
        "hidden" => [
            "securityIgnoreWindowsServerRunningAsSystem" => "bool",
            "disableRehike" => "bool"
        ]
    ];

    // Old config compatibility map
    // These are PropertyAtPath (JS-style) paths
    public static array $compatibilityMap = [
        "useRingoBranding" => "appearance.modernLogo",
        "uploadMenuType" => "appearance.uploadButtonType",
        "versionInFooter" => "appearance.showVersionInFooter",
        "useReturnYouTubeDislike" => "appearance.useRyd",
        "enableRehikeDebugger" => "advanced.enableDebugger",
        "largeSearchThumbs" => "appearance.largeSearchResults",
        "byTextOnByline" => "appearance.usernamePrepends",
        "noViewsText" => "appearance.noViewsText",
        "movingThumbnails" => "appearance.movingThumbnails",
        "hhCSSFixes" => "appearance.cssFixes",
        "watchSidebarDates" => "appearance.watchSidebarDates",
        "teaserReplies" => "appearance.teaserReplies",
        "oldBestOfYouTubeIcons" => "appearance.oldBestOfYouTubeIcons",
        "guideOnWatchPage" => "REMOVE",
        "useWebV2HomeEndpoint" => "REMOVE",
        "useGridHomeStyle" => "REMOVE",
        "accountPickerYtStudio" => "REMOVE",
        "general.teaserReplies" => "REMOVE",
        "appearance.allCommentsLink" => "REMOVE"
    ];
    
    /**
     * If configuration doesn't exist upon
     * attempt to load it, save it
     */
    public static function loadConfig(): object
    {
        if (!file_exists( self::$file ))
        {
            static::dumpDefaultConfig();
        }

        parent::loadConfig();

        $redump = false;
        
        // Make sure new defaults get added to the config file.
        // json_encode wrapped in json_decode as an quick 'n easy
        // way to cast all associative arrays to objects
        foreach (json_decode(json_encode(self::$defaultConfig)) as $key => $value)
        {
            if (!isset(self::$config->{$key}))
            {
                self::$config->{$key} = $value;
                
                $redump = true;
            }
            else
            foreach (self::$defaultConfig[$key] as $option => $val)
            {
                if (!isset(self::$config->{$key}->{$option}))
                {
                    self::$config->{$key}->{$option} = $val;

                    $redump = true;
                }
            }
        }

        foreach (self::$compatibilityMap as $key => $value) {
            try {
                if ($value == "REMOVE") {
                    PropertyAtPath::unset(self::$config, $key);
                } else {
                    $oldCfg = PropertyAtPath::get(self::$config, $key);
                    if ($oldCfg !== null) {
                        PropertyAtPath::set(self::$config, $value, $oldCfg);
                        PropertyAtPath::unset(self::$config, $key);
                    }
                }

                $redump = true;
            } catch (\YukisCoffee\PropertyAtPathException $e) {}
        }


        if ($redump) self::dumpConfig();

        return self::$config;
    }
}