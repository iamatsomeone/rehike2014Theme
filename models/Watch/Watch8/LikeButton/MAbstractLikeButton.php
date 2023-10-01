<?php
namespace Rehike\Model\Watch\Watch8\LikeButton;

use Rehike\Model\Common\MToggleButton;

/**
 * Define an abstract actual "like button" button (also used for dislikes).
 */
class MAbstractLikeButton extends MToggleButton
{
    protected $hideNotToggled = true;

    public $style = "opacity";
    public $icon;
    public $attributes = [
        "orientation" => "vertical",
        "position" => "bottomright",
        "force-position" => "true"
    ];

    public function __construct($type, $active, $count, $state)
    {
        parent::__construct($state);

        $this->icon = (object) [];

        $class = "like-button-renderer-" . $type;
        $this->class[] = $class;
        $this->class[] = $class . "-" . ($active ? "clicked" : "unclicked");
        if ($active)
            $this->class[] = "yt-uix-button-toggled";

        if (!is_null($count))
            $this->setText(number_format($count));
    }
}