<?php

namespace CalendarBundle\Defaults;

use CalendarBundle\Entity\CalendarOption;

/**
 * Class OptionMap
 * @package CalendarBundle\Defaults
 * @author Callum Jones <cj@icj.me>
 */
class OptionMap
{
    /**
     * Default Options, as specified in ical source, with a few modifications to my preferences ;P
     *
     * @var array
     */
    private static $defaults = [
        "DefaultEarlyWarning" => 1,
        "DefaultAlarms"       => [0, 5, 10, 15],
        "DayviewTimeStart"    => 8,
        "DayviewTimeFinish"   => 18,
        "ItemWidth"           => 9,
        "NoticeHeight"        => 6,
        "AmPm"                => false,
        "MondayFirst"         => true,
        "AllowOverflow"       => true,
        "Visible"             => "1", // @TODO find out what this does
        "IgnoreAlarms"        => false,
        "Color"               => "<Default> <Default>",
        "Timezone"            => "<Local>",
    ];

    /**
     * Get Defaults.
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return self::$defaults;
    }

    public function mergeDefaults(array $options)
    {
        $merged = $this->getDefaults();

        foreach ($options as $option) {
            if ($option instanceof CalendarOption) {
                $merged[$option->getName()] = $option->getValue();
            }
        }
    }
}
