<?php

namespace Spatie\CalendarLinks\Generators;

use Spatie\CalendarLinks\Link;
use Spatie\CalendarLinks\Generator;

/**
 * @see https://icalendar.org/RFC-Specifications/iCalendar-RFC-5545/
 */
class Ics implements Generator
{
    public function generate(Link $link): string
    {
	    /** @see https://tools.ietf.org/html/rfc5545.html#section-3.6.1 */
        $url = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:'.$this->generateEventUid($link),
	        'DTSTAMP:'.date('Ymd\THis'),
            'SUMMARY:'.$this->escapeString($link->title),
        ];

        if ($link->allDay) {
            $dateTimeFormat = 'Ymd';
            $url[] = 'DTSTART:'.$link->from->format($dateTimeFormat);
            $url[] = 'DURATION:P1D';
        } else {
            $dateTimeFormat = "e:Ymd\THis";
            $url[] = 'DTSTART;TZID='.$link->from->format($dateTimeFormat);
            $url[] = 'DTEND;TZID='.$link->to->format($dateTimeFormat);
        }

        if ($link->description) {
            $url[] = 'DESCRIPTION:'.$this->escapeString($link->description);
        }
        if ($link->address) {
            $url[] = 'LOCATION:'.$this->escapeString($link->address);
        }

        $url[] = 'END:VEVENT';
        $url[] = 'END:VCALENDAR';
        $redirectLink = implode('\r\n', $url);

	    return 'data:text/calendar;charset=utf8;base64,'.base64_encode($redirectLink);
    }

    /** @see https://tools.ietf.org/html/rfc5545.html#section-3.3.11 */
    protected function escapeString(string $field): string
    {
        return addcslashes($field, "\r\n,;");
    }

    /** @see https://tools.ietf.org/html/rfc5545#section-3.8.4.7 */
    protected function generateEventUid(Link $link): string
    {
        return md5($link->from->format(\DateTime::ATOM).$link->to->format(\DateTime::ATOM).$link->title.$link->address);
    }
}
